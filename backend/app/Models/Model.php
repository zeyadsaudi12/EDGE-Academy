<?php

namespace App\Models;

use App\Core\Database;
use MongoDB\BSON\ObjectId;

abstract class Model {
    protected static $collectionName = '';

    public static function getCollection() {
        return Database::getCollection(static::$collectionName);
    }

    // Convert MongoDB document to plain array, changing ObjectId and UTCDateTime to strings
    public static function toArray($document) {
        if ($document === null) return null;
        
        if ($document instanceof ObjectId) {
            return (string)$document;
        }
        
        if ($document instanceof \MongoDB\BSON\UTCDateTime) {
            return $document->toDateTime()->format('c');
        }

        if ($document instanceof \MongoDB\Model\BSONDocument || $document instanceof \MongoDB\Model\BSONArray) {
            $document = iterator_to_array($document);
        }

        if (is_object($document)) {
            $document = (array)$document;
        }

        if (!is_array($document)) {
            return $document;
        }

        $array = [];
        foreach ($document as $key => $value) {
            if ($value instanceof ObjectId) {
                $array[$key] = (string)$value;
            } elseif ($value instanceof \MongoDB\BSON\UTCDateTime) {
                $array[$key] = $value->toDateTime()->format('c');
            } elseif ($value instanceof \MongoDB\BSON\Decimal128) {
                $array[$key] = (float)(string)$value;
            } elseif (is_array($value) || is_object($value)) {
                $array[$key] = self::toArray($value);
            } else {
                $array[$key] = $value;
            }
        }

        // Standardize _id to string if it exists
        if (isset($array['_id'])) {
            $array['_id'] = (string)$array['_id'];
        }

        return $array;
    }

    // Convert a list of documents to arrays
    public static function toArrayMultiple($documents) {
        $result = [];
        foreach ($documents as $doc) {
            $result[] = self::toArray($doc);
        }
        return $result;
    }
}
