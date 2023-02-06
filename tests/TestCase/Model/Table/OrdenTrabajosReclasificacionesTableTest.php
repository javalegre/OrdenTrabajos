<?php
namespace Ordenes\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Ordenes\Model\Table\OrdenTrabajosReclasificacionesTable;

/**
 * Ordenes\Model\Table\OrdenTrabajosReclasificacionesTable Test Case
 */
class OrdenTrabajosReclasificacionesTableTest extends TestCase
{
    /**
     * Test subject
     *
     * @var \Ordenes\Model\Table\OrdenTrabajosReclasificacionesTable
     */
    public $OrdenTrabajosReclasificaciones;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.Ordenes.OrdenTrabajosReclasificaciones',
        'plugin.Ordenes.Establecimientos',
        'plugin.Ordenes.Users',
        'plugin.Ordenes.OrdenTrabajosReclasificacionesDetalles',
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('OrdenTrabajosReclasificaciones') ? [] : ['className' => OrdenTrabajosReclasificacionesTable::class];
        $this->OrdenTrabajosReclasificaciones = TableRegistry::getTableLocator()->get('OrdenTrabajosReclasificaciones', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->OrdenTrabajosReclasificaciones);

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
}
