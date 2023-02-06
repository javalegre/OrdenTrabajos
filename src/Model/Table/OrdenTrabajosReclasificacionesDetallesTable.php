<?php
namespace Ordenes\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use SoftDelete\Model\Table\SoftDeleteTrait;

/**
 * OrdenTrabajosReclasificacionesDetalles Model
 *
 * @property \Ordenes\Model\Table\OrdenTrabajosReclasificacionesTable&\Cake\ORM\Association\BelongsTo $OrdenTrabajosReclasificaciones
 * @property \Ordenes\Model\Table\OrdenTrabajosTable&\Cake\ORM\Association\BelongsTo $OrdenTrabajos
 * @property \Ordenes\Model\Table\OrdenTrabajoDistribucionesTable&\Cake\ORM\Association\BelongsTo $OrdenTrabajoDistribuciones
 * @property \App\Model\Table\ProyectosTable&\Cake\ORM\Association\BelongsTo $Proyectos
 * @property \App\Model\Table\ProyectosLaboresTable&\Cake\ORM\Association\BelongsTo $ProyectosLabores
 *
 * @method \Ordenes\Model\Entity\OrdenTrabajosReclasificacionesDetalle get($primaryKey, $options = [])
 * @method \Ordenes\Model\Entity\OrdenTrabajosReclasificacionesDetalle newEntity($data = null, array $options = [])
 * @method \Ordenes\Model\Entity\OrdenTrabajosReclasificacionesDetalle[] newEntities(array $data, array $options = [])
 * @method \Ordenes\Model\Entity\OrdenTrabajosReclasificacionesDetalle|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Ordenes\Model\Entity\OrdenTrabajosReclasificacionesDetalle saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Ordenes\Model\Entity\OrdenTrabajosReclasificacionesDetalle patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Ordenes\Model\Entity\OrdenTrabajosReclasificacionesDetalle[] patchEntities($entities, array $data, array $options = [])
 * @method \Ordenes\Model\Entity\OrdenTrabajosReclasificacionesDetalle findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class OrdenTrabajosReclasificacionesDetallesTable extends Table
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

        $this->setTable('orden_trabajos_reclasificaciones_detalles');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Ordenes.OrdenTrabajosReclasificaciones', [
            'foreignKey' => 'orden_trabajos_reclasificacione_id'
        ]);
        $this->belongsTo('Ordenes.OrdenTrabajos', [
            'foreignKey' => 'orden_trabajo_id'
        ]);
        $this->belongsTo('Ordenes.OrdenTrabajosDistribuciones', [
            'foreignKey' => 'orden_trabajos_distribucione_id'
        ]);
        $this->belongsTo('Proyectos', [
            'foreignKey' => 'proyecto_id'
        ]);
        $this->belongsTo('ProyectosLabores', [
            'foreignKey' => 'proyectos_labore_id'
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
            ->allowEmptyString('id', null, 'create');

        $validator
            ->scalar('referencia')
            ->allowEmptyString('referencia');

        $validator
            ->scalar('observaciones')
            ->allowEmptyString('observaciones');

        $validator
            ->dateTime('deleted')
            ->allowEmptyDateTime('deleted');

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
        $rules->add($rules->existsIn(['orden_trabajos_reclasificacione_id'], 'OrdenTrabajosReclasificaciones'));
        $rules->add($rules->existsIn(['orden_trabajo_id'], 'OrdenTrabajos'));
        // $rules->add($rules->existsIn(['orden_trabajo_distribucione_id'], 'OrdenTrabajoDistribuciones'));
        $rules->add($rules->existsIn(['proyecto_id'], 'Proyectos'));
        $rules->add($rules->existsIn(['proyectos_labore_id'], 'ProyectosLabores'));

        return $rules;
    }
}
