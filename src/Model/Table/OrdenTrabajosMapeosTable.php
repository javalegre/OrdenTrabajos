<?php
namespace Ordenes\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;

/**
 * OrdenTrabajosMapeos Model
 *
 * @property \Ordenes\Model\Table\OrdenTrabajosTable&\Cake\ORM\Association\BelongsTo $OrdenTrabajos
 * @property \Ordenes\Model\Table\OrdenTrabajosDistribucionesTable&\Cake\ORM\Association\BelongsTo $OrdenTrabajosDistribuciones
 * @property \Ordenes\Model\Table\OrdenTrabajosCertificacionesTable&\Cake\ORM\Association\BelongsTo $OrdenTrabajosCertificaciones
 * @property \Ordenes\Model\Table\LotesTable&\Cake\ORM\Association\BelongsTo $Lotes
 * @property \Ordenes\Model\Table\ProyectosLaboresTable&\Cake\ORM\Association\BelongsTo $ProyectosLabores
 * @property \Ordenes\Model\Table\CampaniasTiposTable&\Cake\ORM\Association\BelongsTo $CampaniasTipos
 * @property \Ordenes\Model\Table\CalidadMapeosTable&\Cake\ORM\Association\BelongsTo $CalidadMapeos
 * @property \Ordenes\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \Ordenes\Model\Entity\OrdenTrabajosMapeo get($primaryKey, $options = [])
 * @method \Ordenes\Model\Entity\OrdenTrabajosMapeo newEntity($data = null, array $options = [])
 * @method \Ordenes\Model\Entity\OrdenTrabajosMapeo[] newEntities(array $data, array $options = [])
 * @method \Ordenes\Model\Entity\OrdenTrabajosMapeo|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Ordenes\Model\Entity\OrdenTrabajosMapeo saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Ordenes\Model\Entity\OrdenTrabajosMapeo patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Ordenes\Model\Entity\OrdenTrabajosMapeo[] patchEntities($entities, array $data, array $options = [])
 * @method \Ordenes\Model\Entity\OrdenTrabajosMapeo findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class OrdenTrabajosMapeosTable extends Table
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

        $this->setTable('orden_trabajos_mapeos');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('OrdenTrabajos', [
            'foreignKey' => 'orden_trabajo_id',
            'className' => 'Ordenes.OrdenTrabajos',
        ]);
        $this->belongsTo('OrdenTrabajosDistribuciones', [
            'foreignKey' => 'orden_trabajos_distribucione_id',
            'className' => 'Ordenes.OrdenTrabajosDistribuciones',
        ]);
        $this->belongsTo('OrdenTrabajosCertificaciones', [
            'foreignKey' => 'orden_trabajos_certificacione_id',
            'className' => 'Ordenes.OrdenTrabajosCertificaciones',
        ]);
        $this->belongsTo('Lotes', [
            'foreignKey' => 'lote_id',
            'className' => 'Ordenes.Lotes',
        ]);
        $this->belongsTo('ProyectosLabores', [
            'foreignKey' => 'proyectos_labore_id',
            'className' => 'Ordenes.ProyectosLabores',
        ]);
        $this->belongsTo('MapeosCampaniasTipos', [
            'foreignKey' => 'mapeos_campanias_tipo_id',
            'className' => 'Ordenes.MapeosCampaniasTipos',
        ]);
        $this->belongsTo('MapeosCalidades', [
            'foreignKey' => 'mapeos_calidade_id',
            'className' => 'Ordenes.MapeosCalidades',
        ]);
        $this->belongsTo('MapeosProblemas', [
            'foreignKey' => 'mapeos_problema_id',
            'className' => 'Ordenes.MapeosProblemas',
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'className' => 'Ordenes.Users',
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
            ->numeric('superficie')
            ->allowEmptyString('superficie');

        $validator
            ->allowEmptyString('sms');

        $validator
            ->allowEmptyString('pdf');

        $validator
            ->scalar('comentario')
            ->allowEmptyString('comentario');

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
        $rules->add($rules->existsIn(['orden_trabajos_distribucione_id'], 'OrdenTrabajosDistribuciones'));
        $rules->add($rules->existsIn(['orden_trabajos_certificacione_id'], 'OrdenTrabajosCertificaciones'));
        $rules->add($rules->existsIn(['lote_id'], 'Lotes'));
        $rules->add($rules->existsIn(['proyectos_labore_id'], 'ProyectosLabores'));
        $rules->add($rules->existsIn(['mapeos_campanias_tipo_id'], 'MapeosCampaniasTipos'));
        $rules->add($rules->existsIn(['mapeos_calidade_id'], 'MapeosCalidades'));
        $rules->add($rules->existsIn(['mapeos_problema_id'], 'MapeosProblemas'));
        $rules->add($rules->existsIn(['user_id'], 'Users'));

        return $rules;
    }
}
