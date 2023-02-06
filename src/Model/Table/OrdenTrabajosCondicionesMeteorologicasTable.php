<?php
namespace Ordenes\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * OrdenTrabajosCondicionesMeteorologicas Model
 *
 * @property \App\Model\Table\OrdenTrabajosTable|\Cake\ORM\Association\BelongsTo $OrdenTrabajos
 * @property \App\Model\Table\UsersTable|\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\OrdenTrabajosCondicionesMeteorologica get($primaryKey, $options = [])
 * @method \App\Model\Entity\OrdenTrabajosCondicionesMeteorologica newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosCondicionesMeteorologica[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosCondicionesMeteorologica|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OrdenTrabajosCondicionesMeteorologica|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OrdenTrabajosCondicionesMeteorologica patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosCondicionesMeteorologica[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosCondicionesMeteorologica findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class OrdenTrabajosCondicionesMeteorologicasTable extends Table
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

        $this->setTable('orden_trabajos_condiciones_meteorologicas');
        $this->setDisplayField('orden_trabajo_id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('OrdenTrabajos', [
            'foreignKey' => 'orden_trabajo_id',
            'joinType' => 'INNER',
			'className' => 'Ordenes.OrdenTrabajos'
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER'
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
            ->date('fecha')
            ->allowEmpty('fecha');

        $validator
            ->integer('temperatura')
            ->allowEmpty('temperatura');

        $validator
            ->integer('humedad')
            ->allowEmpty('humedad');

        $validator
            ->scalar('viento')
            ->maxLength('viento', 50)
            ->allowEmpty('viento');

        $validator
            ->scalar('direccion')
            ->maxLength('direccion', 50)
            ->allowEmpty('direccion');

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
        $rules->add($rules->existsIn(['orden_trabajo_id'], 'Ordenes.OrdenTrabajos'));
        $rules->add($rules->existsIn(['user_id'], 'Users'));

        return $rules;
    }
}
