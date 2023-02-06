<?php
namespace Ordenes\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use SoftDelete\Model\Table\SoftDeleteTrait;

/**
 * OrdenTrabajosInsumosEntregas Model
 *
 * @property \App\Model\Table\OrdenTrabajosInsumosTable|\Cake\ORM\Association\BelongsTo $OrdenTrabajosInsumos
 * @property \App\Model\Table\ProductosTable|\Cake\ORM\Association\BelongsTo $Productos
 * @property \App\Model\Table\UnidadesTable|\Cake\ORM\Association\BelongsTo $Unidades
 * @property \App\Model\Table\UsersTable|\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\OrdenTrabajosInsumosEntrega get($primaryKey, $options = [])
 * @method \App\Model\Entity\OrdenTrabajosInsumosEntrega newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosInsumosEntrega[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosInsumosEntrega|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OrdenTrabajosInsumosEntrega|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OrdenTrabajosInsumosEntrega patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosInsumosEntrega[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosInsumosEntrega findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class OrdenTrabajosInsumosEntregasTable extends Table
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
        
        $this->setTable('orden_trabajos_insumos_entregas');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Ordenes.OrdenTrabajosInsumos', [
            'foreignKey' => 'orden_trabajos_insumo_id'
        ]);
        $this->belongsTo('Productos', [
            'foreignKey' => 'producto_id'
        ]);
        $this->belongsTo('Unidades', [
            'foreignKey' => 'unidade_id'
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
//            ->dateTime('fecha')
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
        $rules->add($rules->existsIn(['unidade_id'], 'Unidades'));
        $rules->add($rules->existsIn(['user_id'], 'Users'));

        return $rules;
    }
    
//    /**
//     * Devuelvo la cantidad de productos entregados
//     * 
//     */
//    public function findEntregas(Query $query, $options = [])
//    {
//        $query = $this->find('all')
//            ->select(['entregas' => $query->func()->sum('cantidad')])
//            ->where(['orden_trabajos_insumo_id' => $options['id']])
//            ->first();
//        return $query;
//    }        
}
