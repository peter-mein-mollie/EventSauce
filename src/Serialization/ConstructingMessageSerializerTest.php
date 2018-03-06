<?php

namespace EventSauce\EventSourcing\Serialization;

use EventSauce\EventSourcing\DotSeparatedSnakeCaseInflector;
use EventSauce\EventSourcing\EventStub;
use EventSauce\EventSourcing\Header;
use EventSauce\EventSourcing\Message;
use EventSauce\EventSourcing\Time\TestClock;
use EventSauce\EventSourcing\UuidAggregateRootId;
use PHPUnit\Framework\TestCase;
use function iterator_to_array;

class ConstructingMessageSerializerTest extends TestCase
{
    /**
     * @test
     */
    public function serializing_messages_with_aggregate_root_ids()
    {
        $aggregateRootId = UuidAggregateRootId::create();
        $inflector = new DotSeparatedSnakeCaseInflector();
        $aggregateRootIdType = $inflector->instanceToType($aggregateRootId);
        $timeOfRecording = (new TestClock())->pointInTime();
        $message = new Message(new EventStub($timeOfRecording, 'original value'), [
            Header::AGGREGATE_ROOT_ID      => $aggregateRootId->toString(),
            Header::AGGREGATE_ROOT_ID_TYPE => $aggregateRootIdType,
            Header::TIME_OF_RECORDING      => $timeOfRecording->toString(),
            Header::EVENT_TYPE             => $inflector->classNameToType(EventStub::class),
        ]);
        $serializer = new ConstructingMessageSerializer();
        $serialized = $serializer->serializeMessage($message);
        $deserializedMessage = iterator_to_array($serializer->unserializePayload($serialized))[0];
        $messageWithConstructedAggregateRootId = $message->withHeader(Header::AGGREGATE_ROOT_ID, $aggregateRootId);
        $this->assertEquals($messageWithConstructedAggregateRootId, $deserializedMessage);
    }
}
