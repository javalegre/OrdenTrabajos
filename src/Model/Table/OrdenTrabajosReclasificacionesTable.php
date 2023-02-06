<?php
namespace Ordenes\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use SoftDelete\Model\Table\SoftDeleteTrait;

/**
 * OrdenTrabajosReclasificaciones Model
 *
 * @property \Ordenes\Model\Table\EstablecimientosTable&\Cake\ORM\Association\BelongsTo $Establecimientos
 * @property \Ordenes\Model\Table\UsersTable&\Cake\ORM\Association\BelongsTo $Users
 * @property \Ordenes\Model\Table\OrdenTrabajosReclasificacionesDetallesTable&\Cake\ORM\Association\HasMany $OrdenTrabajosReclasificacionesDetalles
 *
 * @method \Ordenes\Model\Entity\OrdenTrabajosReclasificacione get($primaryKey, $options = [])
 * @method \Ordenes\Model\Entity\OrdenTrabajosReclasificacione newEntity($data = null, array $options = [])
 * @method \Ordenes\Model\Entity\OrdenTrabajosReclasificacione[] newEntities(array $data, array $options = [])
 * @method \Ordenes\Model\Entity\OrdenTrabajosReclasificacione|false save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Ordenes\Model\Entity\OrdenTrabajosReclasificacione saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \Ordenes\Model\Entity\OrdenTrabajosReclasificacione patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \Ordenes\Model\Entity\OrdenTrabajosReclasificacione[] patchEntities($entities, array $data, array $options = [])
 * @method \Ordenes\Model\Entity\OrdenTrabajosReclasificacione findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class OrdenTrabajosReclasificacionesTable extends Table
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

        $this->setTable('orden_trabajos_reclasificaciones');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Establecimientos', [
            'foreignKey' => 'establecimiento_id'
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id'
        ]);
        $this->hasMany('Ordenes.OrdenTrabajosReclasificacionesDetalles', [
            'foreignKey' => 'orden_trabajos_reclasificacione_id'
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
            ->scalar('nombre')
            ->maxLength('nombre', 50)
            ->allowEmptyString('nombre');

        $validator
            ->date('fecha')
            ->allowEmptyDate('fecha');

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
        $rules->add($rules->existsIn(['establecimiento_id'], 'Establecimientos'));
        $rules->add($rules->existsIn(['user_id'], 'Users'));

        return $rules;
    }
}
