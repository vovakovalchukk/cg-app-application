<?php
namespace CG\UkMail\Response\Rest;

use CG\UkMail\Consignment\Document;
use CG\UkMail\Consignment\Identifier;
use CG\UkMail\Consignment\Label;
use CG\UkMail\Response\AbstractRestResponse;
use CG\UkMail\Response\ResponseInterface;

class InternationalConsignment extends AbstractRestResponse implements ResponseInterface
{
    /** @var Identifier[] */
    protected $identifiers;
    /** @var Document[] */
    protected $documents;
    /** @var Label[]  */
    protected $labels;

    public function __construct(array $identifiers, array $documents, array $labels)
    {
        $this->identifiers = $identifiers;
        $this->documents = $documents;
        $this->labels = $labels;
    }

    public static function createResponse($response): ResponseInterface
    {
        $identifiers = [];
        foreach ($response['identifiers'] as $identifier) {
            $identifiers[] = new Identifier(
                $identifier['identifierType'],
                $identifier['identifierValue']
            );
        }

        $documents = [];
        foreach ($response['documents'] as $document) {
            $documents[] = new Document($document);
        }

        $labels = [];
        foreach ($response['labels'] as $label) {
            $labels[] = new Label($label);
        }

        return new static($identifiers, $documents, $labels);
    }

    /**
     * @return Identifier[]
     */
    public function getIdentifiers(): array
    {
        return $this->identifiers;
    }

    /**
     * @return Label[]
     */
    public function getLabels(): array
    {
        return $this->labels;
    }
}