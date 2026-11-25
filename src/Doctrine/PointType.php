<?php

namespace App\Doctrine;

use Doctrine\DBAL\Platforms\AbstractPlatform;
use Doctrine\DBAL\Types\Type;

final class PointType extends Type
{
    public const NAME = 'point';

    public function getSQLDeclaration(array $column, AbstractPlatform $platform): string
    {
        return 'POINT';
    }

    public function convertToDatabaseValue($value, AbstractPlatform $platform): ?string
    {
        if (!$value instanceof Point) {
            return null;
        }

        return sprintf('POINT(%F %F)', $value->longitude, $value->latitude);
    }

    public function convertToDatabaseValueSQL(string $sqlExpr, AbstractPlatform $platform): string
    {
        return sprintf('ST_GeomFromText(%s)', $sqlExpr);
    }

    public function convertToPHPValueSQL(string $sqlExpr, AbstractPlatform $platform): string
    {
        return sprintf('ST_AsText(%s)', $sqlExpr);
    }

    public function convertToPHPValue($value, AbstractPlatform $platform): ?Point
    {
        if ($value === null) {
            return null;
        }

        sscanf($value, 'POINT(%f %f)', $longitude, $latitude);

        return new Point((float) $longitude, (float) $latitude);
    }
}