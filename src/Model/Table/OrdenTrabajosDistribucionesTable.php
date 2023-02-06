<?php
namespace Ordenes\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use SoftDelete\Model\Table\SoftDeleteTrait;

/**
 * OrdenTrabajosDistribuciones Model
 *
 * @property \App\Model\Table\OrdenTrabajosTable|\Cake\ORM\Association\BelongsTo $OrdenTrabajos
 * @property |\Cake\ORM\Association\BelongsTo $ProyectosLabores
 * @property \App\Model\Table\UnidadesTable|\Cake\ORM\Association\BelongsTo $Unidades
 * @property \App\Model\Table\ProyectosTable|\Cake\ORM\Association\BelongsTo $Proyectos
 * @property \App\Model\Table\LotesTable|\Cake\ORM\Association\BelongsTo $Lotes
 * @property \App\Model\Table\MonedasTable|\Cake\ORM\Association\BelongsTo $Monedas
 * @property \App\Model\Table\OrdenTrabajosCertificacionesTable|\Cake\ORM\Association\HasMany $OrdenTrabajosCertificaciones
 * @property \App\Model\Table\OrdenTrabajosInsumosTable|\Cake\ORM\Association\HasMany $OrdenTrabajosInsumos
 *
 * @method \App\Model\Entity\OrdenTrabajosDistribucione get($primaryKey, $options = [])
 * @method \App\Model\Entity\OrdenTrabajosDistribucione newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosDistribucione[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosDistribucione|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OrdenTrabajosDistribucione|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OrdenTrabajosDistribucione patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosDistribucione[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosDistribucione findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class OrdenTrabajosDistribucionesTable extends Table
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
        
        /* Agrego duplicado para alquiler de implemento */
        $this->addBehavior('Duplicatable.Duplicatable', [
            'finder' => 'all',
            'remove' => ['created','modified','oracle_oc']
        ]);
        
        $this->setTable('orden_trabajos_distribuciones');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('OrdenTrabajos', [
            'foreignKey' => 'orden_trabajo_id',
            'className' => 'Ordenes.OrdenTrabajos'
        ]);
        $this->belongsTo('ProyectosLabores', [
            'foreignKey' => 'proyectos_labore_id'
        ]);
        $this->belongsTo('Unidades', [
            'foreignKey' => 'unidade_id'
        ]);
        $this->belongsTo('Proyectos', [
            'foreignKey' => 'proyecto_id'
        ]);
        $this->belongsTo('Lotes', [
            'foreignKey' => 'lote_id'
        ]);
        $this->belongsTo('Monedas', [
            'foreignKey' => 'moneda_id'
        ]);
        $this->hasMany('OrdenTrabajosCertificaciones', [
            'foreignKey' => 'orden_trabajos_distribucione_id',
            'className' => 'Ordenes.OrdenTrabajosCertificaciones'
        ]);
        $this->hasMany('Ordenes.OrdenTrabajosInsumos', [
            'foreignKey' => 'orden_trabajos_distribucione_id'
        ]);
        $this->hasMany('Ordenes.OrdenTrabajosReclasificacionesDetalles', [
            'foreignKey' => 'orden_trabajos_distribucione_id'
        ]);
        $this->belongsTo('TecnicasAplicaciones', [
            'foreignKey' => 'tecnicas_aplicacione_id'
        ]);
        $this->hasOne('OrdenTrabajosDistribucionesTarifarios', [
            'foreignKey' => 'orden_trabajos_distribucione_id',
            'dependent' => true,
            'cascadeCallbacks' => true,
            'className' => 'Ordenes.OrdenTrabajosDistribucionesTarifarios'
        ]);

        $this->hasOne('Ordenes.OrdenTrabajosMapeos', [
            'foreignKey' => 'orden_trabajos_distribucione_id'
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
            ->numeric('superficie')
            ->allowEmpty('superficie');

        $validator
            ->numeric('importe')
            ->allowEmpty('importe');

        $validator
            ->allowEmpty('oracle_oc');

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
        $rules->add($rules->existsIn(['orden_trabajo_id'], 'OrdenTrabajos'));
        $rules->add($rules->existsIn(['proyectos_labore_id'], 'ProyectosLabores'));
        $rules->add($rules->existsIn(['unidade_id'], 'Unidades'));
        $rules->add($rules->existsIn(['proyecto_id'], 'Proyectos'));
        $rules->add($rules->existsIn(['lote_id'], 'Lotes'));
        $rules->add($rules->existsIn(['moneda_id'], 'Monedas'));
/*        $rules->add($rules->existsIn(['tecnicas_aplicacione_id'], 'TecnicasAplicaciones')); */
        
        return $rules;
    }
    /**
     * Devuelvo el valor promediado con el que se certificó
     * 
     */
    public function findImporteCertificado(Query $query, $options = [])
    {
        $query = $this->OrdenTrabajosCertificaciones->find('all')
            ->select(['importe' => $query->func()->avg('precio_final')])
            ->where(['orden_trabajos_distribucione_id' => $options['IdDistribucion']])
            ->first();
            
        return $query;
    }
    /**
     * Devuelvo la cantidad de Hectareas certificadas
     * 
     */
    public function findSuperficieCertificada(Query $query, $options = [])
    {
        $query = $this->OrdenTrabajosCertificaciones->find('all')
            ->select(['superficie' => $query->func()->sum('has')])
            ->where(['orden_trabajos_distribucione_id' => $options['IdDistribucion']])
            ->first();
            
        return $query;
    }
    /**
     * Devuelvo el Nombre del Certificador
     * 
     */
    public function findCertificador(Query $query, $options = [])
    {
        $query = $this->OrdenTrabajosCertificaciones->find('all')
            ->contain(['Users'])
            ->select(['Users.nombre'])
            ->where(['orden_trabajos_distribucione_id' => $options['IdDistribucion']])
            ->first();
        return $query;
    }
    
    /*
     *  Cantidad de labores realizadas, de un proyecto especifico
     *  por labor
     */
    public function findLaboresRealizadas(Query $query, $options = [])
    {
        if ($options['Lotes']){
            /* Averiguo que labor corresponde a este proyecto */
            $query = $this->find('all')
                ->select(['ordenados' => $query->func()->sum('superficie')])
                ->where(['proyectos_labore_id' => $options['Labor'], 'proyecto_id' => $options['Proyecto'], 'lote_id IN' => $options['Lotes']])
                ->first();
        } else {
            $query = $this->find('all')
                ->select(['ordenados' => $query->func()->sum('superficie')])
                ->where(['proyectos_labore_id' => $options['Labor'], 'proyecto_id' => $options['Proyecto']])
                ->first();
        }
            
        return $query;
    }
    /*
     *  Cantidad de labores realizadas en el proyecto especificado
     */
    public function findTotalRealizadas(Query $query, $options = [])
    {
        
        if ($options['Lotes']){
            /* Averiguo que labor corresponde a este proyecto */
            $query = $this->find('all')
                ->select(['ordenados' => $query->func()->sum('superficie')])
                ->where(['proyecto_id' => $options['Proyecto'], 'lote_id IN' => $options['Lotes']])
                ->first();

        } else {
            $query = $this->find('all')
                ->select(['ordenados' => $query->func()->sum('superficie')])
                ->where(['proyecto_id' => $options['Proyecto']])
                ->first();
        }
            
        return $query;
    }
    /* Busco la ultima fecha de certificacion */
    public function findFechaCertificacion(Query $query, $options = [])
    {
        $query = $this->OrdenTrabajosCertificaciones->find('all')
            ->select(['fecha_final'])
            ->where(['orden_trabajos_distribucione_id' => $options['IdDistribucion']])
            ->orderAsc('fecha_final')
            ->last();
        return $query;
    }

    /**
     * Chequeo si para el lote y proyecto ya hay una labor realizada.
     * 
     * @param type $proyecto
     * @param type $labor
     * @param type $lote
     */
    public function ChequearLaboresRealizadas($proyecto = null, $labor = null, $lote = null) {
        
        $query = $this->find('all', [
            'conditions' => ['proyecto_id' => $proyecto,
                             'proyectos_labore_id' => $labor,
                             'lote_id' => $lote],
            'contain' => ['OrdenTrabajosCertificaciones', 'OrdenTrabajos' => ['Users' => ['fields' => ['id', 'nombre']]]]
        ]);
        return $query;
    }
}
