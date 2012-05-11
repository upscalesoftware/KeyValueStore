<?php

namespace Doctrine\KeyValueStore\Storage;

/**
 * MongoDb storage
 *
 * @author Markus Bachmann <markus.bachmann@bachi.biz>
 */
class MongoDbStorage implements Storage
{
    /**
     * @var \Mongo
     */
    protected $mongo;

    /**
     * @var array
     */
    protected $dbOptions;

    /**
     * @var \MongoCollection
     */
    protected $collection;

    /**
     * Constructor
     *
     * @param \Mongo $mongo
     * @param array $dbOptions
     */
    public function __construct(\Mongo $mongo, array $dbOptions = array())
    {
        $this->mongo = $mongo;
        $this->dbOptions = array_merge(array(
            'database' => '',
            'collection' => '',
        ), $dbOptions);
    }

    /**
     * Initialize the mongodb collection
     *
     * @throws \RuntimeException
     */
    public function initialize()
    {
        if (null !== $this->collection) {
            return;
        }

        if (empty($this->dbOptions['database'])) {
            throw new \RuntimeException('The option "database" must be set');
        }
        if (empty($this->dbOptions['collection'])) {
            throw new \RuntimeException('The option "collection" must be set');
        }

        $this->collection = $this->mongo->selectDB($this->dbOptions['database'])->selectCollection($this->dbOptions['collection']);
    }

    /**
     * {@inheritDoc}
     */
    public function supportsPartialUpdates()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function supportsCompositePrimaryKeys()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function requiresCompositePrimaryKeys()
    {
        return false;
    }

    /**
     * {@inheritDoc}
     */
    public function insert($storageName, $key, array $data)
    {
        $this->initialize();

        $value = array(
            'key'   => $key,
            'value' => $data,
        );

        $this->collection->insert($value);
    }

    /**
     * {@inheritDoc}
     */
    public function update($storageName, $key, array $data)
    {
        $this->initialize();

        $value = array(
            'key'   => $key,
            'value' => $data,
        );

        $this->collection->update(array('key' => $key), $value);
    }

    /**
     * {@inheritDoc}
     */
    public function delete($storageName, $key)
    {
        $this->initialize();

        $this->collection->remove(array('key' => $key));
    }

    /**
     * {@inheritDoc}
     */
    public function find($storageName, $key)
    {
        $this->initialize();

        $value = $this->collection->findOne(array('key' => $key), array('value'));

        if ($value) {
            return $value['value'];
        }

        return array();
    }

    /**
     * Return a name of the underlying storage.
     *
     * @return string
     */
    public function getName()
    {
        return 'mongodb';
    }
}