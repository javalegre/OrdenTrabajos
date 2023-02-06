<?php
namespace Ordenes\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use SoftDelete\Model\Table\SoftDeleteTrait;

/**
 * OrdenTrabajosInsumosDevoluciones Model
 *
 * @property \App\Model\Table\OrdenTrabajosInsumosTable|\Cake\ORM\Association\BelongsTo $OrdenTrabajosInsumos
 * @property \App\Model\Table\ProductosTable|\Cake\ORM\Association\BelongsTo $Productos
 * @property \App\Model\Table\UsersTable|\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\OrdenTrabajosInsumosDevolucione get($primaryKey, $options = [])
 * @method \App\Model\Entity\OrdenTrabajosInsumosDevolucione newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosInsumosDevolucione[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosInsumosDevolucione|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OrdenTrabajosInsumosDevolucione|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OrdenTrabajosInsumosDevolucione patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosInsumosDevolucione[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosInsumosDevolucione findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class OrdenTrabajosInsumosDevolucionesTable extends Table
{
    use SoftDeleteTrait;
    /**
     * Initialize method
     *
     * @param array $config The configuration for the Table.
     * @return void
     */
    public function initialize(array $config)
    {
        parent::initialize($config);
        $this->addBehavior('AuditStash.AuditLog');
        
        $this->setTable('orden_trabajos_insumos_devoluciones');
        $this->setDisplayField('id');
        $this->setPrimaryKey(['id', 'dispositivo_id']);

        $this->addBehavior('Timestamp');

        $this->belongsTo('OrdenTrabajosInsumos', [
            'foreignKey' => 'orden_trabajos_insumo_id',
            'className' => 'Ordenes.OrdenTrabajosInsumos'
            
        ]);
        $this->belongsTo('Productos', [
            'foreignKey' => 'producto_id'
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id'
        ]);
        $this->belongsTo('Almacenes', [
            'foreignKey' => 'almacene_id'
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
            ->integer('dispositivo_id')
            ->allowEmpty('dispositivo_id', 'create');
        
        $validator
            ->dateTime('fecha')
            ->allowEmpty('fecha');

        $validator
            ->numeric('cantidad')
            ->requirePresence('cantidad', 'create')
            ->notEmpty('cantidad');

        $validator
            ->numeric('transaccion')
            ->allowEmpty('transaccion');

        $validator
            ->scalar('observaciones')
            ->maxLength('observaciones', 16777215)
            ->allowEmpty('observaciones');

        $validator
            ->allowEmpty('deleted');
        
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
        $rules->add($rules->existsIn(['producto_id'], 'Productos'));
        $rules->add($rules->existsIn(['user_id'], 'Users'));

        return $rules;
    }
}
