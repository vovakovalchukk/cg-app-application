<?php
namespace CG\RoyalMailApi\Response;

use CG\RoyalMailApi\Client\AuthToken as Token;
use CG\RoyalMailApi\ResponseInterface;
use CG\RoyalMailApi\Response\FromJsonInterface;
use DateTime;
use stdClass;

class AuthToken implements ResponseInterface, FromJsonInterface
{
    /** @var Token */
    protected $authToken;

    public function __construct(Token $authToken)
    {
        $this->authToken = $authToken;
    }

    public static function fromJson(stdClass $json)
    {
        if (!isset($json->token)) {
            throw new \InvalidArgumentException('JSON returned from client not in expected format');
        }
        $expiryDate = new DateTime('+' . Token::DEFAULT_DURATION);
        $authToken = new Token($json->token, $expiryDate);
        return new self($authToken);
    }

    public function getAuthToken(): Token
    {
        return $this->authToken;
    }

    public function setAuthToken(Token $authToken): AuthToken
    {
        $this->authToken = $authToken;
        return $this;
    }
}