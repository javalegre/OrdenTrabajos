<?php
namespace Ordenes\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * OrdenTrabajosInsumosCostos Model
 *
 * @property \Ordenes\Model\Table\OrdenTrabajosInsumosTable|\Cake\ORM\Association\BelongsTo $OrdenTrabajosInsumos
 * @property \Ordenes\Model\Table\OrdenTrabajosTable|\Cake\ORM\Association\BelongsTo $OrdenTrabajos
 * @property \Ordenes\Model\Table\ProductosTable|\Cake\ORM\Association\BelongsTo $Productos
 * @property \Ordenes\Model\Table\AlmacenesTable|\Cake\ORM\Association\BelongsTo $Almacenes
 *
 * @method \Ordenes\Model\Entity\OrdenTrabajosInsumosCosto get($primaryKey, $options = [])
 * @method \Ordenes\Model\Entity\OrdenTrabajosInsumosCosto newEntity($data = null, array $options = [])
 * @method \Ordenes\Model\Entity\OrdenTrabajosInsumosCosto[] newEntities(array $data, array $options = [])
 * @method \Ordenes\Model\Entity\OrdenTrabajosInsumosCosto|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Ordenes\Model\Entity\OrdenTrabajosInsumosCosto|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Ordenes\Model\Entity\OrdenTrabajosInsumosCosto patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Ordenes\Model\Entity\OrdenTrabajosInsumosCosto[] patchEntities($entities, array $data, array $options = [])
 * @method \Ordenes\Model\Entity\OrdenTrabajosInsumosCosto findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class OrdenTrabajosInsumosCostosTable extends Table
{

    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);

        $this->setTable('orden_trabajos_insumos_costos');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('OrdenTrabajosInsumos', [
            'foreignKey' => 'orden_trabajos_insumo_id',
            'className' => 'Ordenes.OrdenTrabajosInsumos'
        ]);
        $this->belongsTo('OrdenTrabajos', [
            'foreignKey' => 'orden_trabajo_id',
            'className' => 'Ordenes.OrdenTrabajos'
        ]);
        $this->belongsTo('Productos', [
            'foreignKey' => 'producto_id',
            'className' => 'Ordenes.Productos'
        ]);
        $this->belongsTo('Almacenes', [
            'foreignKey' => 'almacene_id',
            'className' => 'Ordenes.Almacenes'
        ]);
    }

    /**
     * Default validation rules.
     *
     * @param \Cake\Validation\Validator $validator Validator instance.
     * @return \Cake\Validation\Validator
     */
    public function validationDefault(Validator $validator)
    {
        $validator
            ->integer('id')
            ->allowEmpty('id', 'create');

        $validator
            ->numeric('cantidad_utilizada')
            ->allowEmpty('cantidad_utilizada');

        $validator
            ->date('fecha')
            ->allowEmpty('fecha');

        $validator
            ->numeric('precio_unitario')
            ->allowEmpty('precio_unitario');

        $validator
            ->numeric('tipo_cambio')
            ->allowEmpty('tipo_cambio');

        $validator
            ->numeric('precio_unitario_dolar')
            ->allowEmpty('precio_unitario_dolar');

        $validator
            ->numeric('superficie')
            ->allowEmpty('superficie');

        $validator
            ->integer('estado')
            ->allowEmpty('estado');

        $validator
            ->integer('periodo')
            ->allowEmpty('periodo');

        return $validator;
    }

    /**
     * Returns a rules checker object that will be used for validating
     * application integrity.
     *
     * @param \Cake\ORM\RulesChecker $rules The rules object to be modified.
     * @return \Cake\ORM\RulesChecker
     */
    public function buildRules(RulesChecker $rules)
    {
        $rules->add($rules->existsIn(['orden_trabajos_insumo_id'], 'OrdenTrabajosInsumos'));
        $rules->add($rules->existsIn(['orden_trabajo_id'], 'OrdenTrabajos'));
        $rules->add($rules->existsIn(['producto_id'], 'Productos'));
        $rules->add($rules->existsIn(['almacene_id'], 'Almacenes'));

        return $rules;
    }
}
