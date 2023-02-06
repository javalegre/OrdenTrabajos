<?php
namespace Ordenes\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use SoftDelete\Model\Table\SoftDeleteTrait;

/**
 * OrdenTrabajosDistribucionesTarifarios Model
 *
 * @property \App\Model\Table\OrdenTrabajosDistribucionesTable|\Cake\ORM\Association\BelongsTo $OrdenTrabajosDistribuciones
 * @property \App\Model\Table\ProyectosLaboresTarifariosTable|\Cake\ORM\Association\BelongsTo $ProyectosLaboresTarifarios
 * @property \App\Model\Table\ProveedoresTable|\Cake\ORM\Association\BelongsTo $Proveedores
 * @property |\Cake\ORM\Association\BelongsTo $OrdenTrabajoAlquilers
 *
 * @method \App\Model\Entity\OrdenTrabajosDistribucionesTarifario get($primaryKey, $options = [])
 * @method \App\Model\Entity\OrdenTrabajosDistribucionesTarifario newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosDistribucionesTarifario[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosDistribucionesTarifario|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OrdenTrabajosDistribucionesTarifario|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OrdenTrabajosDistribucionesTarifario patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosDistribucionesTarifario[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosDistribucionesTarifario findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class OrdenTrabajosDistribucionesTarifariosTable extends Table
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

        $this->setTable('orden_trabajos_distribuciones_tarifarios');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Ordenes.OrdenTrabajosDistribuciones', [
            'foreignKey' => 'orden_trabajos_distribucione_id'
        ]);
        $this->belongsTo('ProyectosLaboresTarifarios', [
            'foreignKey' => 'proyectos_labores_tarifario_id'
        ]);
        $this->belongsTo('Proveedores', [
            'foreignKey' => 'proveedore_id'
        ]);
        $this->belongsTo('Ordenes.OrdenTrabajos', [
            'foreignKey' => 'orden_trabajo_alquiler_id'
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
            ->numeric('tarifa')
            ->allowEmpty('tarifa');

        $validator
            ->allowEmpty('alquiler');

        $validator
            ->numeric('porcentaje')
            ->allowEmpty('porcentaje');

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
        $rules->add($rules->existsIn(['orden_trabajos_distribucione_id'], 'OrdenTrabajosDistribuciones'));
        $rules->add($rules->existsIn(['proyectos_labores_tarifario_id'], 'ProyectosLaboresTarifarios'));
        $rules->add($rules->isUnique(['orden_trabajos_distribucione_id']));
        
        return $rules;
    }
}
