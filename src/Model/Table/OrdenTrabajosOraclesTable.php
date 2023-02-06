<?php
namespace Ordenes\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * OrdenTrabajosOracles Model
 *
 * @property \App\Model\Table\OrdenTrabajosTable|\Cake\ORM\Association\BelongsTo $OrdenTrabajos
 *
 * @method \App\Model\Entity\OrdenTrabajosOracle get($primaryKey, $options = [])
 * @method \App\Model\Entity\OrdenTrabajosOracle newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosOracle[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosOracle|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OrdenTrabajosOracle|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OrdenTrabajosOracle patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosOracle[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosOracle findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class OrdenTrabajosOraclesTable extends Table
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

        $this->setTable('orden_trabajos_oracles');
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
            ->numeric('oc')
            ->requirePresence('oc', 'create')
            ->notEmpty('oc');

        $validator
            //->date('fecha_oc')
            ->requirePresence('fecha_oc', 'create')
            ->notEmpty('fecha_oc');

        $validator
            ->scalar('status')
            ->maxLength('status', 50)
            ->allowEmpty('status');

        $validator
            ->scalar('aprobado')
            ->maxLength('aprobado', 255)
            ->allowEmpty('aprobado');

        $validator
            //->date('fecha_aprobacion')
            ->allowEmpty('fecha_aprobacion');

        $validator
            ->scalar('lote')
            ->maxLength('lote', 50)
            ->allowEmpty('lote');

        $validator
            ->integer('proyecto')
            ->allowEmpty('proyecto');

        $validator
            ->scalar('labor')
            ->maxLength('labor', 100)
            ->allowEmpty('labor');

        $validator
            ->numeric('tc')
            ->allowEmpty('tc');

        $validator
            ->numeric('cantidad')
            ->allowEmpty('cantidad');

        $validator
            ->numeric('precio')
            ->allowEmpty('precio');

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
