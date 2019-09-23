<?php


namespace App\Serializer\Handler;


use JMS\Serializer\Context;
use JMS\Serializer\JsonSerializationVisitor;
use Ramsey\Uuid\UuidInterface;

class UuidHandler
{
    /**
     * @param JsonSerializationVisitor $visitor
     * @param UuidInterface $uuid
     * @param array $type
     * @param Context $context
     * @return string
     */
    public function serializeUuidToJson(JsonSerializationVisitor $visitor, UuidInterface $uuid, array $type, Context $context)
    {
        return $uuid->serialize();
    }
}