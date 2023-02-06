<?php
namespace Ordenes\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Ordenes\Model\Table\OrdenTrabajosReclasificacionesDetallesTable;

/**
 * Ordenes\Model\Table\OrdenTrabajosReclasificacionesDetallesTable Test Case
 */
class OrdenTrabajosReclasificacionesDetallesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \Ordenes\Model\Table\OrdenTrabajosReclasificacionesDetallesTable
     */
    public $OrdenTrabajosReclasificacionesDetalles;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.Ordenes.OrdenTrabajosReclasificacionesDetalles',
        'plugin.Ordenes.OrdenTrabajosReclasificaciones',
        'plugin.Ordenes.OrdenTrabajos',
        'plugin.Ordenes.OrdenTrabajoDistribuciones',
        'plugin.Ordenes.Proyectos',
        'plugin.Ordenes.ProyectosLabores',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('OrdenTrabajosReclasificacionesDetalles') ? [] : ['className' => OrdenTrabajosReclasificacionesDetallesTable::class];
        $this->OrdenTrabajosReclasificacionesDetalles = TableRegistry::getTableLocator()->get('OrdenTrabajosReclasificacionesDetalles', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->OrdenTrabajosReclasificacionesDetalles);

        parent::tearDown();
    }

    /**
     * Test initialize method
     *
     * @return void
     */
    public function testInitialize()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test validationDefault method
     *
     * @return void
     */
    public function testValidationDefault()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test buildRules method
     *
     * @return void
     */
    public function testBuildRules()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test query method
     *
     * @return void
     */
    public function testQuery()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test deleteAll method
     *
     * @return void
     */
    public function testDeleteAll()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test getSoftDeleteField method
     *
     * @return void
     */
    public function testGetSoftDeleteField()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test hardDelete method
     *
     * @return void
     */
    public function testHardDelete()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test hardDeleteAll method
     *
     * @return void
     */
    public function testHardDeleteAll()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }

    /**
     * Test restore method
     *
     * @return void
     */
    public function testRestore()
    {
        $this->markTestIncomplete('Not implemented yet.');
    }
}
