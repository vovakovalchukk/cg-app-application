<?php
namespace CG\UkMail\Response\Rest;

use CG\UkMail\DomesticConsignment\Identifier;
use CG\UkMail\DomesticConsignment\Label;
use CG\UkMail\Response\AbstractRestResponse;
use CG\UkMail\Response\ResponseInterface;

class DomesticConsignment extends AbstractRestResponse implements ResponseInterface
{
    /** @var Identifier[] */
    protected $identifiers;
    /** @var Label[]  */
    protected $labels;

    public function __construct(array $identifiers, array $labels)
    {
        $this->identifiers = $identifiers;
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

        $labels = [];
        foreach ($response['labels'] as $label) {
            $labels[] = new Label($label);
        }

        return new static($identifiers, $labels);
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