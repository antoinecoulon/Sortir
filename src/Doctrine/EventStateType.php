<?php


namespace App\Doctrine;

use App\Enum\EventState;
use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\StringType;

class EventStateType extends StringType
{
    public const NAME = 'event_state';

    public function getName(): string
    {
        return self::NAME;
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform)
    {
        if ($value instanceof EventState) {
            return $value->name;
        }

        throw new \InvalidArgumentException("Invalid state value: $value");
    }

    public function convertToPHPValue($value, AbstractPlatform $platform)
    {
        return EventState::from($value);
    }

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return $platform->getVarcharTypeDeclarationSQL(['length' => 20]);
    }
}