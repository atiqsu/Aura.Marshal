<?php
namespace Aura\Marshal;

use Aura\Marshal\Collection\Builder as CollectionBuilder;
use Aura\Marshal\Entity\Builder as EntityBuilder;
use Aura\Marshal\Entity\GenericCollection;
use Aura\Marshal\Entity\GenericEntity;
use Aura\Marshal\Relation\Builder as RelationBuilder;
use Aura\Marshal\Type\Builder as TypeBuilder;
use Aura\Marshal\Type\GenericType;
use Aura\Marshal\Lazy\Builder as LazyBuilder;

/**
 * Test class for Type.
 * Generated by PHPUnit on 2011-11-21 at 18:02:55.
 */
class TypeTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var GenericType
     */
    protected $type;
    
    /**
     * Sets up the fixture, for example, opens a network connection.
     * This method is called before a test is executed.
     */
    protected function setUp()
    {
        parent::setUp();
        
        $types = include __DIR__ . DIRECTORY_SEPARATOR . 'fixture_types.php';
        $info = $types['posts'];
        
        $this->type = new GenericType;
        $this->type->setIdentityField($info['identity_field']);
        $this->type->setIndexFields($info['index_fields']);
        $this->type->setEntityBuilder(new EntityBuilder(new LazyBuilder));
        $this->type->setCollectionBuilder(new CollectionBuilder);
    }
    
    protected function loadTypeWithPosts()
    {
        $data = include __DIR__ . DIRECTORY_SEPARATOR . 'fixture_data.php';
        $this->type->load($data['posts']);
        return $data['posts'];
    }
    
    /**
     * Tears down the fixture, for example, closes a network connection.
     * This method is called after a test is executed.
     */
    protected function tearDown()
    {
        parent::tearDown();
    }

    public function testSetAndGetIdentityField()
    {
        $expect = 'foobar';
        $this->type->setIdentityField('foobar');
        $actual = $this->type->getIdentityField();
        $this->assertSame($expect, $actual);
    }
    
    public function testSetAndGetIndexFields()
    {
        $expect = ['foobar', 'bazdib'];
        $this->type->setIndexFields($expect);
        $actual = $this->type->getIndexFields();
        $this->assertSame($expect, $actual);
        
    }
    public function testSetAndGetEntityBuilder()
    {
        $builder = new EntityBuilder;
        $this->type->setEntityBuilder($builder);
        $actual = $this->type->getEntityBuilder();
        $this->assertSame($builder, $actual);
    }
    
    public function testSetAndGetCollectionBuilder()
    {
        $builder = new CollectionBuilder;
        $this->type->setCollectionBuilder($builder);
        $actual = $this->type->getCollectionBuilder();
        $this->assertSame($builder, $actual);
    }
    
    public function testSetAndGetLazyBuilder()
    {
        $builder = new LazyBuilder;
        $this->type->setLazyBuilder($builder);
        $actual = $this->type->getLazyBuilder();
        $this->assertSame($builder, $actual);
    }
    
    public function testLoadAndGetStorage()
    {
        $data = $this->loadTypeWithPosts();
        $expect = count($data);
        $actual = count($this->type);
        $this->assertSame($expect, $actual);
        
        // try loading again to make sure we don't double-load.
        // $expect stays as the original count value.
        $this->loadTypeWithPosts();
        $actual = count($this->type);
        $this->assertSame($expect, $actual);
    }
    
    public function testGetIdentityValues()
    {
        $data = $this->loadTypeWithPosts();
        $expect = [1, 2, 3, 4, 5];
        $actual = $this->type->getIdentityValues();
        $this->assertSame($expect, $actual);
    }
    
    public function testGetFieldValues()
    {
        $data = $this->loadTypeWithPosts();
        $expect = [1 => '1', 2 => '1', 3 => '1', 4 => '2', 5 => '2'];
        $actual = $this->type->getFieldValues('author_id');
        $this->assertSame($expect, $actual);
    }
    
    public function testGetEntity()
    {
        $data = $this->loadTypeWithPosts();
        $expect = (object) $data[2];
        $actual = $this->type->getEntity(3);
        
        $this->assertSame($expect->id, $actual->id);
        $this->assertSame($expect->author_id, $actual->author_id);
        $this->assertSame($expect->body, $actual->body);
        
        // get it again for complete code coverage
        $again = $this->type->getEntity(3);
        $this->assertSame($actual, $again);
    }
    
    public function testGetEntity_none()
    {
        $data = $this->loadTypeWithPosts();
        $actual = $this->type->getEntity(999);
        $this->assertNull($actual);
    }
    
    public function testGetEntityByField_identity()
    {
        $data = $this->loadTypeWithPosts();
        $expect = (object) $data[3];
        $actual = $this->type->getEntityByField('id', 4);
        
        $this->assertSame($expect->id, $actual->id);
        $this->assertSame($expect->author_id, $actual->author_id);
        $this->assertSame($expect->body, $actual->body);
    }
    
    public function testGetEntityByField_index()
    {
        $data = $this->loadTypeWithPosts();
        $expect = (object) $data[3];
        $actual = $this->type->getEntityByField('author_id', 2);
        
        $this->assertSame($expect->id, $actual->id);
        $this->assertSame($expect->author_id, $actual->author_id);
        $this->assertSame($expect->body, $actual->body);
    }
    
    public function testGetEntityByField_indexNone()
    {
        $data = $this->loadTypeWithPosts();
        $actual = $this->type->getEntityByField('author_id', 'no such value');
        $this->assertNull($actual);
    }
    
    public function testGetEntityByField_loop()
    {
        $data = $this->loadTypeWithPosts();
        $expect = (object) $data[3];
        $actual = $this->type->getEntityByField('fake_field', '88');
        
        $this->assertSame($expect->id, $actual->id);
        $this->assertSame($expect->author_id, $actual->author_id);
        $this->assertSame($expect->body, $actual->body);
        $this->assertSame($expect->fake_field, $actual->fake_field);
    }
    
    public function testGetEntityByField_loopNone()
    {
        $data = $this->loadTypeWithPosts();
        $actual = $this->type->getEntityByField('fake_field', 'no such value');
        $this->assertNull($actual);
    }
    
    public function getCollection()
    {
        $data = $this->loadTypeWithPosts();
        $collection = $this->type->getCollection([1, 2, 3]);
        $expect = [
            (object) $data[0],
            (object) $data[1],
            (object) $data[2],
        ];
        
        foreach ($collection as $offset => $actual) {
            $this->assertSame($expect[$offset]->id, $actual->id);
            $this->assertSame($expect[$offset]->author_id, $actual->author_id);
            $this->assertSame($expect[$offset]->body, $actual->body);
        }
    }
    
    public function testGetCollectionByField()
    {
        $data = $this->loadTypeWithPosts();
        $collection = $this->type->getCollectionByField('fake_field', 88);
        $expect = [
            (object) $data[3],
            (object) $data[4],
        ];
        
        foreach ($collection as $offset => $actual) {
            $this->assertSame($expect[$offset]->id, $actual->id);
            $this->assertSame($expect[$offset]->author_id, $actual->author_id);
            $this->assertSame($expect[$offset]->body, $actual->body);
            $this->assertSame($expect[$offset]->fake_field, $actual->fake_field);
        }
    }
    
    public function testGetCollectionByField_many()
    {
        $data = $this->loadTypeWithPosts();
        $collection = $this->type->getCollectionByField('fake_field', [88, 69]);
        $expect = [
            (object) $data[0],
            (object) $data[1],
            (object) $data[2],
            (object) $data[3],
            (object) $data[4],
        ];
        
        foreach ($collection as $offset => $actual) {
            $this->assertSame($expect[$offset]->id, $actual->id);
            $this->assertSame($expect[$offset]->author_id, $actual->author_id);
            $this->assertSame($expect[$offset]->body, $actual->body);
            $this->assertSame($expect[$offset]->fake_field, $actual->fake_field);
        }
    }
    
    public function testGetCollectionByField_identity()
    {
        $data = $this->loadTypeWithPosts();
        $collection = $this->type->getCollectionByField('id', [4, 5]);
        $expect = [
            (object) $data[3],
            (object) $data[4],
        ];
        
        foreach ($collection as $offset => $actual) {
            $this->assertSame($expect[$offset]->id, $actual->id);
            $this->assertSame($expect[$offset]->author_id, $actual->author_id);
            $this->assertSame($expect[$offset]->body, $actual->body);
            $this->assertSame($expect[$offset]->fake_field, $actual->fake_field);
        }
    }
    
    public function getCollectionByField_index()
    {
        $data = $this->loadTypeWithPosts();
        $collection = $this->type->getCollectionByField('author_id', [2, 1]);
        $expect = [
            (object) $data[3],
            (object) $data[4],
            (object) $data[0],
            (object) $data[1],
            (object) $data[2],
        ];
        
        foreach ($collection as $offset => $actual) {
            $this->assertSame($expect[$offset]->id, $actual->id);
            $this->assertSame($expect[$offset]->author_id, $actual->author_id);
            $this->assertSame($expect[$offset]->body, $actual->body);
            $this->assertSame($expect[$offset]->fake_field, $actual->fake_field);
        }
    }
    
    public function testAddAndGetRelation()
    {
        $type_builder = new TypeBuilder;
        $relation_builder = new RelationBuilder;
        $types = include __DIR__ . DIRECTORY_SEPARATOR . 'fixture_types.php';
        $manager = new Manager($type_builder, $relation_builder, $types);
        
        $name = 'meta';
        $info = $types['posts']['relation_names'][$name];
        
        $relation = $relation_builder->newInstance('posts', $name, $info, $manager);
        $this->type->setRelation($name, $relation);
        
        $actual = $this->type->getRelation('meta');
        $this->assertSame($relation, $actual);
        
        // try again again, should fail
        $this->setExpectedException('Aura\Marshal\Exception');
        $this->type->setRelation($name, $relation);
    }
    
    public function testTypeBuilder_noIdentityField()
    {
        $type_builder = new TypeBuilder;
        $this->setExpectedException('Aura\Marshal\Exception');
        $type = $type_builder->newInstance([]);
    }
    
    public function testNewEntity()
    {
        $this->loadTypeWithPosts();
        $before = count($this->type);
        
        // do we actually get a new entity back?
        $entity = $this->type->newEntity();
        $this->assertInstanceOf('Aura\Marshal\Entity\GenericEntity', $entity);
        
        // has it been added to the identity map?
        $expect = $before + 1;
        $actual = count($this->type);
        $this->assertSame($expect, $actual);
    }
    
    public function testGetChangedEntities()
    {
        $data = $this->loadTypeWithPosts();
        
        // change entity id 1 and 3
        $entity_1 = $this->type->getEntity(1);
        $entity_1->fake_field = 'changed';
        $entity_3 = $this->type->getEntity(3);
        $entity_3->fake_field = 'changed';
        
        // get entity 2 but don't change it
        $entity_2 = $this->type->getEntity(2);
        $fake_field = $entity_2->fake_field;
        $entity_2->fake_field = $fake_field;
        
        // now check for changes
        $expect = [
            $entity_1->id => $entity_1,
            $entity_3->id => $entity_3,
        ];
        
        $actual = $this->type->getChangedEntities();
        $this->assertSame($expect, $actual);
    }

    public function testGetChangedEntities_empty()
    {
        $data = $this->loadTypeWithPosts();
        $expect = [];

        $actual = $this->type->getChangedEntities();
        $this->assertSame($expect, $actual);
    }
    
    public function testGetNewEntities()
    {
        $data = $this->loadTypeWithPosts();
        $expect = [
            $this->type->newEntity(['fake_field' => 101]),
            $this->type->newEntity(['fake_field' => 102]),
            $this->type->newEntity(['fake_field' => 105]),
        ];
        $actual = $this->type->getNewEntities();
        $this->assertSame($expect, $actual);
    }

    public function testGetNewEntities_empty()
    {
        $data = $this->loadTypeWithPosts();
        $expect = [];
        $actual = $this->type->getNewEntities();
        $this->assertSame($expect, $actual);
    }
    
    public function testGetInitialData_noEntity()
    {
        $entity = new \StdClass;
        $this->assertNull($this->type->getInitialData($entity));
    }
    
    public function testGetChangedFields_numeric()
    {
        $this->loadTypeWithPosts();
        $entity = $this->type->getEntity(1);
        
        // change from string '69' to int 69;
        // it should not be marked as a change
        $entity->fake_field = 69;
        $expect = [];
        $actual = $this->type->getChangedFields($entity);
        $this->assertSame($expect, $actual);
        
        $entity->fake_field = 4.56;
        $expect = ['fake_field' => 4.56];
        $actual = $this->type->getChangedFields($entity);
        $this->assertSame($expect, $actual);
    }
    
    public function testGetChangedFields_toNull()
    {
        $this->loadTypeWithPosts();
        $entity = $this->type->getEntity(1);
        
        $entity->fake_field = null;
        $expect = ['fake_field' => null];
        $actual = $this->type->getChangedFields($entity);
        $this->assertSame($expect, $actual);
    }
    
    public function testGetChangedFields_fromNull()
    {
        $this->loadTypeWithPosts();
        $entity = $this->type->getEntity(1);
        
        $entity->null_field = 0;
        $expect = ['null_field' => 0];
        $actual = $this->type->getChangedFields($entity);
        $this->assertSame($expect, $actual);
    }
    
    public function testGetChangedFields_other()
    {
        $this->loadTypeWithPosts();
        $entity = $this->type->getEntity(1);
        
        $entity->fake_field = 'changed';
        $expect = ['fake_field' => 'changed'];
        $actual = $this->type->getChangedFields($entity);
        $this->assertSame($expect, $actual);
    }
    
    public function testLoadEntity()
    {
        $initial_data = [
            'id'  => 88,
            'author_id' => 69,
            'foo' => 'bar',
            'baz' => 'dib',
            'zim' => 'gir',
        ];
        
        $entity = $this->type->loadEntity($initial_data);
        foreach ($initial_data as $field => $value) {
            $this->assertSame($value, $entity->$field);
        }
    }
    
    public function testLoadCollection()
    {
        $data = include __DIR__ . DIRECTORY_SEPARATOR . 'fixture_data.php';
        $collection = $this->type->loadCollection($data['posts']);
        $this->assertInstanceOf(
            'Aura\Marshal\Collection\GenericCollection',
            $collection
        );
    }

    public function testRemove_none()
    {
        $this->loadTypeWithPosts();

        $this->assertSame([], $this->type->getRemovedEntities());
    }

    public function testRemove_single()
    {
        $this->loadTypeWithPosts();

        $this->assertTrue($this->type->removeEntity(1));

        $this->assertSame([1], array_keys($this->type->getRemovedEntities()));
    }

    public function testRemove_many()
    {
        $this->loadTypeWithPosts();

        $this->assertTrue($this->type->removeEntity(1));
        $this->assertTrue($this->type->removeEntity(2));
        $this->assertTrue($this->type->removeEntity(3));

        $this->assertSame(
            [1, 2, 3],
            array_keys($this->type->getRemovedEntities())
        );
    }

    public function testRemoveNonExistent()
    {
        $this->loadTypeWithPosts();

        $this->assertFalse($this->type->removeEntity(99999));
    }

    public function testRemoveAndGet()
    {
        $this->loadTypeWithPosts();
        $this->assertTrue($this->type->removeEntity(1));

        $this->assertNull($this->type->getEntity(1));
    }

    public function testRemoveAndDeleteAgain()
    {
        $this->loadTypeWithPosts();
        $this->assertTrue($this->type->removeEntity(1));
        $this->assertFalse($this->type->removeEntity(1));
    }

    public function testRemoveEmpty()
    {
        $this->assertFalse($this->type->removeEntity(1));
    }

    public function testRemoveAndGetCollectionByIndex_first()
    {
        $data = $this->loadTypeWithPosts();

        $expect = [
            (object) $data[1],
            (object) $data[2]
        ];

        $this->assertTrue($this->type->removeEntity(1));

        $collection = $this->type->getCollectionByField('author_id', 1);

        foreach ($collection as $offset => $actual) {
            $this->assertSame($expect[$offset]->id, $actual->id);
            $this->assertSame($expect[$offset]->author_id, $actual->author_id);
            $this->assertSame($expect[$offset]->body, $actual->body);
            $this->assertSame($expect[$offset]->fake_field, $actual->fake_field);
        }
    }

    public function testRemoveAndGetCollectionByIndex_second()
    {
        $data = $this->loadTypeWithPosts();

        $expect = [
            (object) $data[0],
            (object) $data[2]
        ];

        $this->assertTrue($this->type->removeEntity(2));

        $collection = $this->type->getCollectionByField('author_id', 1);

        foreach ($collection as $offset => $actual) {
            $this->assertSame($expect[$offset]->id, $actual->id);
            $this->assertSame($expect[$offset]->author_id, $actual->author_id);
            $this->assertSame($expect[$offset]->body, $actual->body);
            $this->assertSame($expect[$offset]->fake_field, $actual->fake_field);
        }
    }

    public function testRemoveAndGetCollectionByField_first()
    {
        $data = $this->loadTypeWithPosts();

        $expect = [
            (object) $data[4]
        ];

        $this->assertTrue($this->type->removeEntity(4));

        $collection = $this->type->getCollectionByField('fake_field', 88);

        foreach ($collection as $offset => $actual) {
            $this->assertSame($expect[$offset]->id, $actual->id);
            $this->assertSame($expect[$offset]->author_id, $actual->author_id);
            $this->assertSame($expect[$offset]->body, $actual->body);
            $this->assertSame($expect[$offset]->fake_field, $actual->fake_field);
        }
    }

    public function testRemoveAndGetCollectionByField_second()
    {
        $data = $this->loadTypeWithPosts();

        $expect = [
            (object) $data[0],
            (object) $data[2],
        ];

        $this->assertTrue($this->type->removeEntity(2));

        $collection = $this->type->getCollectionByField('fake_field', 69);

        foreach ($collection as $offset => $actual) {
            $this->assertSame($expect[$offset]->id, $actual->id);
            $this->assertSame($expect[$offset]->author_id, $actual->author_id);
            $this->assertSame($expect[$offset]->body, $actual->body);
            $this->assertSame($expect[$offset]->fake_field, $actual->fake_field);
        }
    }

    public function testRemoveAndGetCollectionByField_many()
    {
        $data = $this->loadTypeWithPosts();

        $expect = [
            (object) $data[0],
            (object) $data[2],
            (object) $data[4]
        ];

        $this->assertTrue($this->type->removeEntity(2));
        $this->assertTrue($this->type->removeEntity(4));

        $collection = $this->type->getCollectionByField('fake_field', [88, 69]);

        foreach ($collection as $offset => $actual) {
            $this->assertSame($expect[$offset]->id, $actual->id);
            $this->assertSame($expect[$offset]->author_id, $actual->author_id);
            $this->assertSame($expect[$offset]->body, $actual->body);
            $this->assertSame($expect[$offset]->fake_field, $actual->fake_field);
        }
    }

    public function testRemoveAll()
    {
        $data = $this->loadTypeWithPosts();

        foreach ($data as $post) {
            $this->assertTrue($this->type->removeEntity($post['id']));
        }

        $this->assertSame(0, $this->type->count());

        $this->assertNull($this->type->getEntity(1));
    }
}
