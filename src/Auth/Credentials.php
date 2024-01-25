<?php

namespace Fakturoid\Auth;

use DateTimeImmutable;
use Fakturoid\Enum\AuthTypeEnum;
use Fakturoid\Exception\InvalidDataException;
use JsonException;

class Credentials
{
    public function __construct(
        private readonly ?string $refresh_token,
        private readonly ?string $access_token,
        private readonly ?int $expires_in,
        private DateTimeImmutable $lastValidation,
        private AuthTypeEnum $authType
    ) {
    }

    public function getRefreshToken(): ?string
    {
        return $this->refresh_token;
    }

    public function getAccessToken(): ?string
    {
        return $this->access_token;
    }

    public function isExpired(): bool
    {
        return (new DateTimeImmutable()) > $this->lastValidation->modify('+' . ($this->expires_in - 10) . ' seconds');
    }

    public function getAuthType(): AuthTypeEnum
    {
        return $this->authType;
    }

    public function setAuthType(AuthTypeEnum $type): void
    {
        $this->authType = $type;
    }

    /**
     * @throws InvalidDataException
     */
    public function toJson(): string
    {
        try {
            $json = json_encode([
                'refresh_token' => $this->refresh_token,
                'access_token' => $this->access_token,
                'expires_in' => $this->expires_in,
                'lastValidation' => $this->lastValidation->format(DateTimeImmutable::ATOM),
                'authType' => $this->authType,
            ], JSON_THROW_ON_ERROR);
        } catch (JsonException $exception) {
            throw new InvalidDataException('Failed to encode credentials to JSON', $exception->getCode(), $exception);
        }
        return $json;
    }

    public function addLastValidation(DateTimeImmutable $lastValidation): void
    {
        $this->lastValidation = $lastValidation;
    }
}