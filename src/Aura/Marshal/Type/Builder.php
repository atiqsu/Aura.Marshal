<?php
/**
 * 
 * This file is part of the Aura project for PHP.
 * 
 * @package Aura.Marshal
 * 
 * @license http://opensource.org/licenses/bsd-license.php BSD
 * 
 */
namespace Aura\Marshal\Type;

use Aura\Marshal\Collection\Builder as CollectionBuilder;
use Aura\Marshal\Exception;
use Aura\Marshal\Record\Builder as RecordBuilder;
use Aura\Marshal\Proxy\Builder as ProxyBuilder;

/**
 * 
 * Builds a type object from an array of description information.
 * 
 * @package Aura.Marshal
 * 
 */
class Builder
{
    /**
     * 
     * Returns a new type instance.
     * 
     * The `$info` array should have four keys:
     * 
     * - `'identity_field'` (string): The name of the identity field for 
     *   records of this type. This key is required.
     * 
     * - `record_builder` (Record\BuilderInterface): A builder to create
     *   record objects for the type. This key is optional, and defaults to a
     *   new Record\Builder object.
     * 
     * - `collection_builder` (Collection\BuilderInterface): A 
     *   A builder to create collection objects for the type. This key
     *   is optional, and defaults to a new Collection\Builder object.
     * 
     * @param array $info An array of information about the type.
     * 
     * @return GenericType
     * 
     */
    public function newInstance($info)
    {
        $base = [
            'identity_field'        => null,
            'index_fields'          => [],
            'record_builder'        => null,
            'collection_builder'    => null,
            'proxy_builder'         => null,
        ];

        $info = array_merge($base, $info);

        if (! $info['identity_field']) {
            throw new Exception('No identity field specified.');
        }

        if (! $info['record_builder']) {
            $info['record_builder'] = new RecordBuilder(new ProxyBuilder);
        }

        if (! $info['collection_builder']) {
            $info['collection_builder'] = new CollectionBuilder;
        }

        if (! $info['proxy_builder']) {
            $info['proxy_builder'] = new ProxyBuilder;
        }

        $type = new GenericType;
        $type->setIdentityField($info['identity_field']);
        $type->setIndexFields($info['index_fields']);
        $type->setRecordBuilder($info['record_builder']);
        $type->setCollectionBuilder($info['collection_builder']);
        $type->setProxyBuilder($info['proxy_builder']);
        
        return $type;
    }
}
