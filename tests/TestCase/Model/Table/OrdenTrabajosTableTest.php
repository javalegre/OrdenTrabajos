<?php
namespace Ordenes\Test\TestCase\Model\Table;

use Cake\ORM\TableRegistry;
use Cake\TestSuite\TestCase;
use Ordenes\Model\Table\OrdenTrabajosTable;

/**
 * Ordenes\Model\Table\OrdenTrabajosTable Test Case
 */
class OrdenTrabajosTableTest extends TestCase
{

    /**
     * Test subject
     *
     * @var \Ordenes\Model\Table\OrdenTrabajosTable
     */
    public $OrdenTrabajos;

    /**
     * Fixtures
     *
     * @var array
     */
    public $fixtures = [
        'plugin.ordenes.orden_trabajos',
        'plugin.ordenes.orden_trabajos_estados',
        'plugin.ordenes.establecimientos',
        'plugin.ordenes.proveedores',
        'plugin.ordenes.orden_trabajos_dataloads',
        'plugin.ordenes.users',
        'plugin.ordenes.orden_trabajos_auditorias',
        'plugin.ordenes.orden_trabajos_certificaciones',
        'plugin.ordenes.orden_trabajos_certificaciones_imagenes',
        'plugin.ordenes.orden_trabajos_condiciones_meteorologicas',
        'plugin.ordenes.orden_trabajos_cuadrillas',
        'plugin.ordenes.orden_trabajos_distribuciones',
        'plugin.ordenes.orden_trabajos_insumos',
        'plugin.ordenes.orden_trabajos_insumos_costos',
        'plugin.ordenes.orden_trabajos_oracles',
        'plugin.ordenes.orden_trabajos_oracles_rechazos',
        'plugin.ordenes.silobolsas_embolsados',
        'plugin.ordenes.labores'
    ];

    /**
     * setUp method
     *
     * @return void
     */
    public function setUp()
    {
        parent::setUp();
        $config = TableRegistry::getTableLocator()->exists('OrdenTrabajos') ? [] : ['className' => OrdenTrabajosTable::class];
        $this->OrdenTrabajos = TableRegistry::getTableLocator()->get('OrdenTrabajos', $config);
    }

    /**
     * tearDown method
     *
     * @return void
     */
    public function tearDown()
    {
        unset($this->OrdenTrabajos);

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
