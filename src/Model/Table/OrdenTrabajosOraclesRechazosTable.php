<?php
namespace Ordenes\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * OrdenTrabajosOraclesRechazos Model
 *
 * @property \App\Model\Table\OrdenTrabajosTable|\Cake\ORM\Association\BelongsTo $OrdenTrabajos
 *
 * @method \App\Model\Entity\OrdenTrabajosOraclesRechazo get($primaryKey, $options = [])
 * @method \App\Model\Entity\OrdenTrabajosOraclesRechazo newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosOraclesRechazo[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosOraclesRechazo|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OrdenTrabajosOraclesRechazo|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OrdenTrabajosOraclesRechazo patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosOraclesRechazo[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosOraclesRechazo findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class OrdenTrabajosOraclesRechazosTable extends Table
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

        $this->setTable('orden_trabajos_oracles_rechazos');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Ordenes.OrdenTrabajos', [
            'foreignKey' => 'orden_trabajo_id'
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
            ->integer('orden_compra')
            ->allowEmpty('orden_compra');

        $validator
            ->dateTime('fecha')
            ->allowEmpty('fecha');

        $validator
            ->scalar('rechazado_por')
            ->maxLength('rechazado_por', 100)
            ->allowEmpty('rechazado_por');

        $validator
            ->scalar('evento')
            ->maxLength('evento', 50)
            ->allowEmpty('evento');

        $validator
            ->scalar('status')
            ->maxLength('status', 50)
            ->allowEmpty('status');

        $validator
            ->scalar('motivo')
            ->allowEmpty('motivo');

        $validator
            ->integer('procesado')
            ->allowEmpty('procesado');

        $validator
            ->dateTime('deleted')
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
        $rules->add($rules->existsIn(['orden_trabajo_id'], 'OrdenTrabajos'));

        return $rules;
    }
}
