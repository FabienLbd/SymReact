<?php

namespace App\Serializer;

use Symfony\Component\Serializer\Exception\InvalidArgumentException;
use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\CacheableSupportsMethodInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/**
 * Normalizes an object implementing the {@see \DateTimeInterface} to a date string.
 * Denormalizes a date string to an instance of {@see \DateTime} or {@see \DateTimeImmutable}.
 *
 * @author Kévin Dunglas <dunglas@gmail.com>
 */
class PatchedDateTimeNormalizer implements NormalizerInterface, DenormalizerInterface, CacheableSupportsMethodInterface
{
    final public const FORMAT_KEY = 'datetime_format';
    final public const TIMEZONE_KEY = 'datetime_timezone';

    private array $defaultContext;

    private static array $supportedTypes = [
        \DateTimeInterface::class => true,
        \DateTimeImmutable::class => true,
        \DateTime::class => true,
    ];

    public function __construct(array $defaultContext = [], \DateTimeZone $timezone = null)
    {
        $this->defaultContext = [
            self::FORMAT_KEY => \DateTime::RFC3339,
            self::TIMEZONE_KEY => null,
        ];

        if (!\is_array($defaultContext)) {
            @trigger_error('Passing configuration options directly to the constructor is deprecated since Symfony 4.2, use the default context instead.', E_USER_DEPRECATED);

            $defaultContext = [self::FORMAT_KEY => (string) $defaultContext];
            $defaultContext[self::TIMEZONE_KEY] = $timezone;
        }

        $this->defaultContext = array_merge($this->defaultContext, $defaultContext);
    }

    /**
     * @throws InvalidArgumentException
     */
    public function normalize($object, $format = null, array $context = [])
    {
        if (!$object instanceof \DateTimeInterface) {
            throw new InvalidArgumentException('The object must implement the "\DateTimeInterface".');
        }

        $dateTimeFormat = $context[self::FORMAT_KEY] ?? $this->defaultContext[self::FORMAT_KEY];
        $timezone = $this->getTimezone($context);

        if (null !== $timezone) {
            $object = clone $object;
            $object = $object->setTimezone($timezone);
        }

        return $object->format($dateTimeFormat);
    }

    public function supportsNormalization($data, $format = null): bool
    {
        return $data instanceof \DateTimeInterface;
    }

    /**
     * @throws NotNormalizableValueException
     */
    public function denormalize($data, $type, $format = null, array $context = []): object
    {
        $dateTimeFormat = $context[self::FORMAT_KEY] ?? null;
        $timezone = $this->getTimezone($context);

        if ('' === $data || null === $data) {
            throw new NotNormalizableValueException('The data is either an empty string or null, you should pass a string that can be parsed with the passed format or a valid DateTime string.');
        }

        if (null !== $dateTimeFormat) {
            $object = \DateTime::class === $type ? \DateTime::createFromFormat($dateTimeFormat, $data, $timezone) : \DateTimeImmutable::createFromFormat($dateTimeFormat, $data, $timezone);

            if (false !== $object) {
                return $object;
            }

            $dateTimeErrors = \DateTime::class === $type ? \DateTime::getLastErrors() : \DateTimeImmutable::getLastErrors();

            throw new NotNormalizableValueException(sprintf('Parsing datetime string "%s" using format "%s" resulted in %d errors:'."\n".'%s', $data, $dateTimeFormat, $dateTimeErrors['error_count'], implode("\n", $this->formatDateTimeErrors($dateTimeErrors['errors']))));
        }

        try {
            return \DateTime::class === $type ? new \DateTime($data, $timezone) : new \DateTimeImmutable($data, $timezone);
        } catch (\Exception $e) {
            if ($context['disable_type_enforcement'] ?? false) {
                return $data;
            }
            throw new NotNormalizableValueException($e->getMessage(), $e->getCode(), $e);
        }
    }

    public function supportsDenormalization($data, $type, $format = null): bool
    {
        return isset(self::$supportedTypes[$type]);
    }

    public function hasCacheableSupportsMethod(): bool
    {
        return self::class === static::class;
    }

    /**
     * Formats datetime errors.
     *
     * @return string[]
     */
    private function formatDateTimeErrors(array $errors): array
    {
        $formattedErrors = [];

        foreach ($errors as $pos => $message) {
            $formattedErrors[] = sprintf('at position %d: %s', $pos, $message);
        }

        return $formattedErrors;
    }

    private function getTimezone(array $context): ?\DateTimeZone
    {
        $dateTimeZone = $context[self::TIMEZONE_KEY] ?? $this->defaultContext[self::TIMEZONE_KEY];

        if (null === $dateTimeZone) {
            return null;
        }

        return $dateTimeZone instanceof \DateTimeZone ? $dateTimeZone : new \DateTimeZone($dateTimeZone);
    }
}
