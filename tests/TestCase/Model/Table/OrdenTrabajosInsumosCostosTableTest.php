<?php
namespace Ordenes\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Ordenes\Model\Table\OrdenTrabajosInsumosCostosTable;

/**
 * Ordenes\Model\Table\OrdenTrabajosInsumosCostosTable Test Case
 */
class OrdenTrabajosInsumosCostosTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \Ordenes\Model\Table\OrdenTrabajosInsumosCostosTable
     */
    public $OrdenTrabajosInsumosCostos;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.ordenes.orden_trabajos_insumos_costos',
        'plugin.ordenes.orden_trabajos_insumos',
        'plugin.ordenes.orden_trabajos',
        'plugin.ordenes.productos',
        'plugin.ordenes.almacenes'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('OrdenTrabajosInsumosCostos') ? [] : ['className' => OrdenTrabajosInsumosCostosTable::class];
        $this->OrdenTrabajosInsumosCostos = TableRegistry::getTableLocator()->get('OrdenTrabajosInsumosCostos', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->OrdenTrabajosInsumosCostos);

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
