<?php
namespace Products\Product;

use CG\Channel\Gearman\Generator\UnimportedProduct\Import as UnimportedProductImportGenerator;
use CG\Product\Unimported\Entity as UnimportedProduct;
use CG\Stdlib\Log\LoggerAwareInterface;
use CG\Stdlib\Log\LogTrait;
use CG\User\ActiveUserInterface;
use function CG\Stdlib\getLinesFromString;

class Importer implements LoggerAwareInterface
{
    use LogTrait;

    protected const LOG_CODE_IMPORT_STARTED = 'ProductImport::Started';
    protected const LOG_MSG_IMPORT_STARTED = 'Started to process a ProductImport CSV for user %d';
    protected const LOG_CODE_IMPORT_FINISHED = 'ProductImport::Finished';
    protected const LOG_MSG_IMPORT_FINISHED = 'Finished processing the ProductImport CSV for user %d';

    public const HEADER_TITLE = 'Title';
    public const HEADER_SKU = 'SKU';
    public const HEADER_QTY = 'Stock Quantity';
    public const HEADER_VARIATION_SET = 'Variation Set';
    public const HEADER_VARIATION_ATTR_REGEX = '/^Variation:\s*(\S+.*)$/';

    protected const HEADERS = [
        self::HEADER_TITLE => 'validateString',
        self::HEADER_SKU => 'validateString',
        self::HEADER_QTY => 'validateInteger',
    ];

    /** @var ActiveUserInterface */
    protected $activeUser;
    /** @var Importer\Mapper */
    protected $mapper;
    /** @var UnimportedProductImportGenerator */
    protected $unimportedProductImportGenerator;

    public function __construct(
        ActiveUserInterface $activeUser,
        Importer\Mapper $mapper,
        UnimportedProductImportGenerator $unimportedProductImportGenerator
    ) {
        $this->activeUser = $activeUser;
        $this->mapper = $mapper;
        $this->unimportedProductImportGenerator = $unimportedProductImportGenerator;
    }

    public function importProductsFromCsvString(string $productCsv): Importer\Status
    {
        return $this->importProducts(getLinesFromString($productCsv));
    }

    public function importProducts(iterable $productIterator): Importer\Status
    {
        $status = new Importer\Status();
        try {
            $this->addGlobalLogEventParam('ou', $this->activeUser->getCompanyId());
            $this->logDebug(static::LOG_MSG_IMPORT_STARTED, ['user' => $this->activeUser->getActiveUser()->getId()], static::LOG_CODE_IMPORT_STARTED);

            $headers = null;
            $previousLine = null;
            $variations = [];
            foreach ($this->getValidCsvLines($productIterator) as $index => $productLine) {
                if ($headers === null) {
                    if (!$this->validateHeaders($status, $headers = $productLine)) {
                        break;
                    }
                    continue;
                }

                $productLine = $this->normaliseProductLine($productLine, $headers);
                $lineId = $index + 1;

                if ($this->hasVariationSetChanged($productLine, $previousLine, $variations)) {
                    $this->importProduct($status, $lineId - 1, $previousLine, $variations);
                    $variations = [];
                }

                if ($this->isVariationLine($productLine)) {
                    $variation = $this->prepareVariation($status, $lineId, $productLine);
                    if (!$variation) {
                        continue;
                    }
                    $variations[] = $variation;
                    $status->lineProcessed();
                    $previousLine = $productLine;
                    continue;
                }

                $success = $this->importProduct($status, $lineId, $productLine);
                if (!$success) {
                    continue;
                }
                $status->lineProcessed();
                $previousLine = $productLine;
            }
            // Handle the last line being part of a variation set
            if (!empty($variations)) {
                $this->importProduct($status, $lineId, $productLine, $variations);
            }
        } finally {
            $this->logDebug(static::LOG_MSG_IMPORT_FINISHED . PHP_EOL . PHP_EOL . $status, ['user' => $this->activeUser->getActiveUser()->getId()], static::LOG_CODE_IMPORT_FINISHED);
            $this->removeGlobalLogEventParam('ou');
        }
        return $status;
    }

    protected function getValidCsvLines(iterable $productIterator): \Generator
    {
        foreach ($productIterator as $index => $productLine) {
            $productLineArray = array_filter(str_getcsv($productLine) ?? []);
            if (empty($productLineArray)) {
                continue;
            }
            yield $productLineArray;
        }
    }

    protected function validateHeaders(Importer\Status $status, array $headers): bool
    {
        $hasMissingHeaders = false;
        foreach (array_keys(static::HEADERS) as $header) {
            if (!in_array($header, $headers)) {
                $hasMissingHeaders = true;
                $status->headerMissing($header);
            }
        }
        if (in_array(static::HEADER_VARIATION_SET, $headers)) {
            $hasMissingVariationHeaders = !$this->validateVariationHeaders($status, $headers);
            $hasMissingHeaders = ($hasMissingHeaders || $hasMissingVariationHeaders);
        }

        return !$hasMissingHeaders;
    }

    protected function validateVariationHeaders(Importer\Status $status, array $headers): bool
    {
        foreach ($headers as $header) {
            if (preg_match(static::HEADER_VARIATION_ATTR_REGEX, $header)) {
                return true;
            }
        }
        $status->headerMissing('Variation Name(s)');
        return false;
    }

    protected function normaliseProductLine(array $productLine, array $headers): array
    {
        if (count($headers) < count($productLine)) {
            array_splice($productLine, count($headers));
        }
        return array_combine($headers, array_pad($productLine, count($headers), ''));
    }

    protected function hasVariationSetChanged(array $productLine, ?array $previousLine, array $variations): bool
    {
        return ($previousLine && !empty($variations) && !$this->isSameVariationSet($productLine, $previousLine));
    }

    protected function isSameVariationSet(array $currentProductLine, array $previousProductLine): bool
    {
        return (
            isset($currentProductLine[static::HEADER_VARIATION_SET]) &&
            $currentProductLine[static::HEADER_VARIATION_SET] == $previousProductLine[static::HEADER_VARIATION_SET]
        );
    }

    protected function prepareVariation(Importer\Status $status, $lineId, array $variationLine): ?UnimportedProduct
    {
        $errors = $this->validateProductLine($variationLine);
        if (!empty($errors)) {
            $status->lineFailed($lineId, $errors);
            return null;
        }
        return $this->mapper->importLineToUnimportedProduct($variationLine);
    }

    protected function importProduct(Importer\Status $status, $lineId, array $productLine, array $variations = []): bool
    {
        $errors = $this->validateProductLine($productLine);
        if (!empty($errors)) {
            $status->lineFailed($lineId, $errors);
            return false;
        }

        ($this->unimportedProductImportGenerator)(
            $this->activeUser->getCompanyId(),
            $this->mapper->importLineToUnimportedProduct($productLine, $variations)
        );
        return true;
    }

    protected function validateProductLine(array $productLine): array
    {
        $errors = [];
        foreach (static::HEADERS as $header => $validator) {
            try {
                $this->{$validator}($productLine[$header]);
            } catch (\InvalidArgumentException $exception) {
                $errors[$header] = $exception->getMessage();
            }
        }
        if ($this->isVariationLine($productLine)) {
            $variationErrors = $this->validateVariationLine($productLine);
            $errors = array_merge($errors, $variationErrors);
        }
        return $errors;
    }

    protected function isVariationLine(array $productLine): bool
    {
        return (isset($productLine[static::HEADER_VARIATION_SET]) && trim($productLine[static::HEADER_VARIATION_SET]) != '');
    }

    protected function validateVariationLine(array $variationLine): array
    {
        $errors = [];
        foreach ($variationLine as $header => $value) {
            if ($header != static::HEADER_VARIATION_SET && !preg_match(static::HEADER_VARIATION_ATTR_REGEX, $header)) {
                continue;
            }
            try {
                $this->validateString($variationLine[$header]);
            } catch (\InvalidArgumentException $exception) {
                $errors[$header] = $exception->getMessage();
            }
        }
        return $errors;
    }

    protected function validateString(&$stringValue)
    {
        if (!is_scalar($stringValue)) {
            throw new \InvalidArgumentException(sprintf('Value is expected to be a string, %s passed', gettype($stringValue)));
        }

        $stringValue = trim($stringValue);
        if (strlen($stringValue) == 0) {
            throw new \InvalidArgumentException('Value can not be empty');
        }
    }

    protected function validateInteger(&$intValue)
    {
        if (!is_scalar($intValue)) {
            throw new \InvalidArgumentException(sprintf('Value is expected to be a number, %s passed', gettype($intValue)));
        }

        if (is_string($intValue) && strlen($intValue) == 0) {
            throw new \InvalidArgumentException('Value can not be empty');
        }

        if (!is_numeric($intValue)) {
            throw new \InvalidArgumentException(sprintf('Value is expected to be a number, %s passed', gettype($intValue)));
        }

        $intValue = intval($intValue);
    }
}