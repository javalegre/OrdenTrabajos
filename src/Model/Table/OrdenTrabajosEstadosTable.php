<?php
namespace Ordenes\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * OrdenTrabajosEstados Model
 *
 * @property \App\Model\Table\OrdenTrabajosTable|\Cake\ORM\Association\HasMany $OrdenTrabajos
 *
 * @method \App\Model\Entity\OrdenTrabajosEstado get($primaryKey, $options = [])
 * @method \App\Model\Entity\OrdenTrabajosEstado newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosEstado[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosEstado|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OrdenTrabajosEstado|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OrdenTrabajosEstado patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosEstado[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosEstado findOrCreate($search, callable $callback = null, $options = [])
 */
class OrdenTrabajosEstadosTable extends Table
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

        $this->setTable('orden_trabajos_estados');
        $this->setDisplayField('nombre');
        $this->setPrimaryKey('id');

        $this->hasMany('OrdenTrabajos', [
            'foreignKey' => 'orden_trabajos_estado_id'
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
            ->scalar('nombre')
            ->maxLength('nombre', 50)
            ->requirePresence('nombre', 'create')
            ->notEmpty('nombre');

        $validator
            ->integer('prioridad')
            ->requirePresence('prioridad', 'create')
            ->notEmpty('prioridad')
            ->add('prioridad', 'unique', ['rule' => 'validateUnique', 'provider' => 'table']);

        $validator
            ->scalar('observaciones')
            ->maxLength('observaciones', 16777215)
            ->allowEmpty('observaciones');

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
        $rules->add($rules->isUnique(['prioridad']));

        return $rules;
    }
}
