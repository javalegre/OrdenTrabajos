<?php
namespace Ordenes\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use SoftDelete\Model\Table\SoftDeleteTrait;

/**
 * OrdenTrabajosCertificaciones Model
 *
 * @property \App\Model\Table\OrdenTrabajosDistribucionesTable|\Cake\ORM\Association\BelongsTo $OrdenTrabajosDistribuciones
 * @property \App\Model\Table\OrdenTrabajosTable|\Cake\ORM\Association\BelongsTo $OrdenTrabajos
 * @property \App\Model\Table\MonedasTable|\Cake\ORM\Association\BelongsTo $Monedas
 * @property \App\Model\Table\UsersTable|\Cake\ORM\Association\BelongsTo $Users
 *
 * @method \App\Model\Entity\OrdenTrabajosCertificacione get($primaryKey, $options = [])
 * @method \App\Model\Entity\OrdenTrabajosCertificacione newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosCertificacione[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosCertificacione|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OrdenTrabajosCertificacione|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OrdenTrabajosCertificacione patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosCertificacione[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosCertificacione findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class OrdenTrabajosCertificacionesTable extends Table
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
        
        $this->setTable('orden_trabajos_certificaciones');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('OrdenTrabajosDistribuciones', [
            'foreignKey' => 'orden_trabajos_distribucione_id',
            'joinType' => 'INNER',
            'className' => 'Ordenes.OrdenTrabajosDistribuciones'
        ]);
        $this->belongsTo('OrdenTrabajos', [
            'foreignKey' => 'orden_trabajo_id',
            'joinType' => 'INNER',
            'className' => 'Ordenes.OrdenTrabajos'
        ]);
        $this->belongsTo('Monedas', [
            'foreignKey' => 'moneda_id'
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
            ->dateTime('fecha_inicio')
            ->allowEmpty('fecha_inicio');

        $validator
            ->dateTime('fecha_final')
            ->allowEmpty('fecha_final');

        $validator
            ->numeric('has')
            ->allowEmpty('has');

        $validator
            ->numeric('tipo_cambio')
            ->allowEmpty('tipo_cambio')
            ->add('tipo_cambio', 'Moneda USD', [
                    'rule' => function($value, $context) {
                
                        /* El tipo de cambio al certificar en USD solo puede ser 1 */
                        if ($context['data']['moneda_id'] === '2') {
                            if ($context['data']['tipo_cambio'] > 1) {
                                return false;
                            }
                        }
                        return true;
                    },
                    'message' => 'El tipo de cambio no puede ser mayor a 1 al certificar en USD.',
            ]);
                    
        $validator
            ->numeric('precio_final')
            ->allowEmpty('precio_final');

        $validator
            ->scalar('observaciones')
            ->maxLength('observaciones', 16777215)
            ->allowEmpty('observaciones');

        $validator
            ->allowEmpty('imagenes');

        $validator
            ->dateTime('deleted')
            ->allowEmpty('deleted');

        $validator
            ->scalar('oracle_flag')
            ->maxLength('oracle_flag', 5)
            ->allowEmpty('oracle_flag');

        $validator
            ->scalar('interface_error')
            ->allowEmpty('interface_error');

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
        $rules->add($rules->existsIn(['orden_trabajo_id'], 'OrdenTrabajos'));
        $rules->add($rules->existsIn(['moneda_id'], 'Monedas'));
        $rules->add($rules->existsIn(['user_id'], 'Users'));
        
//        $validator->add('name', 'myRule', [
//            'rule' => function ($data, $provider) {
//                if ($data > 1) {
//                    return true;
//                }
//                return 'Not a good value.';
//            }
//        ]);
        
//        $rules->add(
//            function ($entity, $options) {
//                if (!$entity->length) {
//                    return false;
//                }
//
//                if ($entity->length < 10) {
//                    return 'Error message when value is less than 10';
//                }
//
//                if ($entity->length > 20) {
//                    return 'Error message when value is greater than 20';
//                }
//
//                return true;
//            },
//            'ruleName',
//            [
//                'errorField' => 'length',
//                'message' => 'Generic error message used when `false` is returned'
//            ]
//         );
        
        return $rules;
    }
}
