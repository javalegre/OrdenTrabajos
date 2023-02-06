<?php
namespace Ordenes\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use Cake\ORM\TableRegistry;
use Cake\Mailer\Email;

/**
 * OrdenTrabajos Model
 *
 * @property \App\Model\Table\OrdenTrabajosEstadosTable|\Cake\ORM\Association\BelongsTo $OrdenTrabajosEstados
 * @property \App\Model\Table\EstablecimientosTable|\Cake\ORM\Association\BelongsTo $Establecimientos
 * @property \App\Model\Table\ProveedoresTable|\Cake\ORM\Association\BelongsTo $Proveedores
 * @property \App\Model\Table\UsersTable|\Cake\ORM\Association\BelongsTo $Users
 * @property |\Cake\ORM\Association\HasMany $OrdenTrabajosCertificaciones
 * @property \App\Model\Table\OrdenTrabajosCuadrillasTable|\Cake\ORM\Association\HasMany $OrdenTrabajosCuadrillas
 * @property \App\Model\Table\OrdenTrabajosDistribucionesTable|\Cake\ORM\Association\HasMany $OrdenTrabajosDistribuciones
 * @property \App\Model\Table\OrdenTrabajosInsumosTable|\Cake\ORM\Association\HasMany $OrdenTrabajosInsumos
 * @property \App\Model\Table\LaboresTable|\Cake\ORM\Association\BelongsToMany $Labores
 *
 * @method \App\Model\Entity\OrdenTrabajo get($primaryKey, $options = [])
 * @method \App\Model\Entity\OrdenTrabajo newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajo[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajo|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OrdenTrabajo|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OrdenTrabajo patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajo[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajo findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */


class OrdenTrabajosTable extends Table
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
        $this->addBehavior('AuditStash.AuditLog');
        
        // add Duplicatable behavior
        $this->addBehavior('Duplicatable.Duplicatable', [
            'finder' => 'all',
            'remove' => ['created','modified','oc','orden_trabajos_dataload_id','oracle_oc_flag','interface_error'],
            // mark invoice as copied
            'set' => [
                'fecha' => function($entity) {
                    $date = date_create();
                    $cadena_fecha_actual = date_format($date, 'Y-m-d');
                    return $cadena_fecha_actual;
                }
            ]
        ]);
        
        $this->setTable('orden_trabajos');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('OrdenTrabajosEstados', [
            'foreignKey' => 'orden_trabajos_estado_id',
            'joinType' => 'INNER',
            'className' => 'Ordenes.OrdenTrabajosEstados'
        ]);
        $this->belongsTo('Establecimientos', [
            'foreignKey' => 'establecimiento_id'
        ]);
        $this->belongsTo('Ordenes.OrdenTrabajosDataloads', [
            'foreignKey' => 'orden_trabajos_dataload_id'
        ]);        
        $this->belongsTo('Proveedores', [
            'foreignKey' => 'proveedore_id'
        ]);
        $this->belongsTo('Users', [
            'foreignKey' => 'user_id',
            'joinType' => 'INNER'
        ]);
        $this->hasMany('OrdenTrabajosCertificaciones', [
            'foreignKey' => 'orden_trabajo_id',
            'className' => 'Ordenes.OrdenTrabajosCertificaciones'
        ]);
        $this->hasMany('OrdenTrabajosCuadrillas', [
            'foreignKey' => 'orden_trabajo_id'
        ]);
        $this->hasMany('OrdenTrabajosDistribuciones', [
            'foreignKey' => 'orden_trabajo_id',
            'className' => 'Ordenes.OrdenTrabajosDistribuciones'
        ]);
        $this->hasMany('OrdenTrabajosCertificacionesImagenes', [
            'foreignKey' => 'orden_trabajo_id'
        ]);        
        $this->hasMany('OrdenTrabajosInsumos', [
            'foreignKey' => 'orden_trabajo_id',
            'className' => 'Ordenes.OrdenTrabajosInsumos'
        ]);
        $this->hasMany('Ordenes.OrdenTrabajosOracles', [
            'foreignKey' => 'orden_trabajo_id'
        ]);
        $this->hasMany('Ordenes.OrdenTrabajosOraclesRechazos', [
            'foreignKey' => 'orden_trabajo_id'
        ]);
        $this->hasOne('OrdenTrabajosCondicionesMeteorologicas', [
            'foreignKey' => 'orden_trabajo_id'
        ]);
        $this->hasOne('Silobolsas.SilobolsasEmbolsados', [
            'foreignKey' => 'orden_trabajo_id'
        ]);
        $this->belongsToMany('Labores', [
            'foreignKey' => 'orden_trabajo_id',
            'targetForeignKey' => 'labore_id',
            'joinTable' => 'orden_trabajos_labores'
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
            //->date('fecha')
            ->allowEmpty('fecha');

        $validator
            ->scalar('descripcion')
            ->allowEmpty('descripcion');

        $validator
            ->numeric('velocidadviento')
            ->allowEmpty('velocidadviento');

        $validator
            ->numeric('temperatura')
            ->allowEmpty('temperatura');

        $validator
            ->numeric('consumogasoil')
            ->allowEmpty('consumogasoil');

        $validator
            ->numeric('humedad')
            ->allowEmpty('humedad');

        $validator
            ->scalar('observaciones')
            ->maxLength('observaciones', 16777215)
            ->allowEmpty('observaciones');
        
        $validator
            ->numeric('oc')
            ->allowEmpty('oc');
        
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
        $rules->add($rules->existsIn(['orden_trabajos_estado_id'], 'OrdenTrabajosEstados'));
        $rules->add($rules->existsIn(['establecimiento_id'], 'Establecimientos'));
        $rules->add($rules->existsIn(['proveedore_id'], 'Proveedores'));
        $rules->add($rules->existsIn(['user_id'], 'Users'));

        return $rules;
    }
    /*
     * Devuelvo true si la linea de distribución se puede certificar
     * 
     */
    public function findCertificable(Query $query, $options = [])
    {
        $certificable = 1;
        $ordenTrabajo = $this->get($options['IdOrden'],[
            'contain' =>(['OrdenTrabajosDistribuciones', 'OrdenTrabajosInsumos' => ['OrdenTrabajosInsumosEntregas']])
        ]);
        $distribuciones = $ordenTrabajo->orden_trabajos_distribuciones;
        if (!$distribuciones) {
            $certificable = 0;
        }
        foreach($ordenTrabajo->orden_trabajos_insumos as $insumo){
            if (!$insumo->orden_trabajos_insumos_entregas){
                $certificable = 0;
            }
        }
        return $certificable;
    }

    /* Total de Aprobadas */
    public function findCantidadBorrador(Query $query){
       return $query->where('orden_trabajos_estado_id = 1')->count();
    }
    /* Total de Abiertas */
    public function findCantidadAbiertas(Query $query){
       return $query->where('orden_trabajos_estado_id = 2')->count();
    }
    /* Total de Borrador */
    public function findCantidadCerradas(Query $query){
       return $query->where('orden_trabajos_estado_id = 3')->count();
    }
    /* Total de Certificadas */
    public function findCantidadCertificadas(Query $query){
       return $query->where(['orden_trabajos_estado_id = 4 and oracle_oc_flag IS NULL'])->count();
    }
    /* Total de Certificadas */
    public function findCantidadCertificadasSinDL(Query $query){
       return $query->where('orden_trabajos_estado_id = 4 and orden_trabajos_dataload_id IS NULL and oracle_oc_flag IS NULL')->count();
    }    
    /* Total de Certificadas */
    public function findCantidadCertificadasConDL(Query $query){
       return $query->where('orden_trabajos_estado_id = 4 and orden_trabajos_dataload_id IS NOT NULL')->count();
    }    
    /* Total de Anulados */
    public function findCantidadAnuladas(Query $query){
       return $query->where('orden_trabajos_estado_id = 5')->count();
    }    
    /**
    * Find neighbors method
    */
   public function findNeighbors(Query $query, $options = [])
   {
       $id = $options['id'];
       $usuarios = $this->NotificarA( $options['user_id'] );
       
       $previous = $this->find()
               ->select('id')
               ->order(['id' => 'DESC'])
               ->where(['id <' => $id, 'orden_trabajos_estado_id' => 3, 'user_id IN' => $usuarios ])
               ->first();
       $next = $this->find()
               ->select('id')
               ->order(['id' => 'ASC'])
               ->where(['id >' => $id, 'orden_trabajos_estado_id' => 3, 'user_id IN' => $usuarios ])
               ->first();
       
       return ['prev' => $previous, 'next' => $next];
   }
    
    /*
     * Busco todos los usuarios que compartan lotes
     * 
     * Devuelvo un array
     */
    function NotificarA($user_id) {
        /* Lista de Usuarios a notificar */
        $usuarios = [];
        array_push( $usuarios, $user_id );
        /* Cargo la tabla Tecnica Responsables para averiguar a quien tengo que
         * tener en cuenta
         */
        $tecnicas = TableRegistry::getTableLocator()->get('Tecnicaresponsables');
        /* Busco los lotes asignados al usuario actual                     */
        $mislotes = $tecnicas->find('all')
                ->where(['Tecnicaresponsables.user_id ' => $user_id]);
        /* Tengo los sectores asociados al usuario actual, ahora busco todos los
         * usuarios asociados a estos lotes                                   */
        foreach ($mislotes as $lote) {
            $misusuarios = $tecnicas->find('all', [
                'conditions' => ['lote_id' => $lote->lote_id, 'user_id !=' => $lote->user_id]
            ]);
            foreach ($misusuarios as $usuario) {
                /* Si el usuario aun no esta en el array y no es el mismo que genera, lo agrego */
                if (!in_array($usuario->user_id, $usuarios)) {
                    array_push($usuarios, $usuario->user_id);
                }
            }
        }
        return $usuarios;
    }
    
    /**
     * Genera un listado de Ordenes de Trabajo 
     * 
     * @param type $establecimiento_id Id del establecimiento
     * @param type $proyecto_id Id del Proyecto
     * @param type $proveedore_id Id de Proveedor
     * @param type $desde Fecha Inicio del reporte
     * @param type $hasta Fecha Final del reporte
     */
    public function ExportarExcel($establecimiento_id = null, $proyecto_id = null, $proveedore_id = null, $desde = null, $hasta = null)
    {
        $query =    "SELECT ot.id                       			AS ot,
                            p.nombre						AS proveedor,
                            e.organizacion					AS org,
                            e.nombre						AS establecimiento,
                            pr.nombre						AS proyecto,
                            pr.cultivo						AS cultivo,
                            pl.nombre                                   	AS labor,
                            l.nombre						AS lote,
                            s.nombre						AS sector,
                            u.nombre				   		AS um,
                            ot.created						AS fecha_ordenado,
                            otd.superficie				 	AS cantidad_ordenado,
                            certif.fecha_aplicacion                             AS fecha_aplicacion,
                            certif.fecha_certificacion                  	AS fecha_certificacion,
                            certif.sup_certificada                              AS cantidad_certificada,
                            certif.moneda					AS moneda,
                            certif.precio_final                                 AS tarifa,
                            (certif.sup_certificada * certif.precio_final)      AS Importe,
                            usr.nombre                                          AS creador,
                            certif.certificador                                 AS aprobador,
                            ote.nombre						AS estado,
                            oto.oc						AS orden_compra,
                            oto.fecha_oc					AS fecha_oc,
                            oto.tc						AS oc_tipo_cambio,
                            oto.`status`					AS oc_status,
                            oto.cantidad					AS oc_cantidad,
                            oto.precio						AS oc_precio,
                            oto.precio * oto.cantidad                           AS oc_otal,
                            ot.observaciones,
                            otcm.fecha						AS cond_fecha,
                            otcm.temperatura					AS cond_temperatura,
                            otcm.humedad					AS cond_humedad,
                            otcm.viento						AS cond_viento,
                            otcm.direccion					AS direccion
                    FROM orden_trabajos								  ot	
                           JOIN proveedores                         p   ON p.id = ot.proveedore_id
                           JOIN orden_trabajos_estados				  ote ON ote.id = ot.orden_trabajos_estado_id
                           JOIN establecimientos                    e   ON e.id = ot.establecimiento_id
                           LEFT JOIN orden_trabajos_distribuciones  otd ON otd.orden_trabajo_id = ot.id AND otd.deleted IS NULL
                           LEFT JOIN proyectos_labores              pl  ON pl.id = otd.proyectos_labore_id
                           LEFT JOIN proyectos                      pr  ON pr.id = otd.proyecto_id
                           LEFT JOIN lotes								  l   ON l.id = otd.lote_id
                           LEFT JOIN sectores							  s	ON s.id = l.sectore_id	
                           LEFT JOIN unidades							  u	ON u.id = otd.unidade_id
                           LEFT JOIN users								  usr ON usr.id = ot.user_id	
                           LEFT JOIN orden_trabajos_oracles			  oto ON oto.orden_trabajo_id = ot.id	AND oto.lote = l.nombre AND oto.labor = pl.nombre
                           LEFT JOIN orden_trabajos_condiciones_meteorologicas otcm ON otcm.orden_trabajo_id = ot.id
                           LEFT JOIN (SELECT orden_trabajos_distribucione_id,
                                                                           MAX(fecha_inicio) AS fecha_aplicacion,  
                                                                           MAX(orden_trabajos_certificaciones.created) as fecha_certificacion, 
                                                                           SUM(has) AS sup_certificada,
                                                                           precio_final,
                                                                           monedas.simbolo AS moneda,
                                                                           users.nombre AS certificador
                                                           FROM orden_trabajos_certificaciones, monedas, users
                                                           WHERE orden_trabajos_certificaciones.moneda_id = monedas.id AND users.id = orden_trabajos_certificaciones.user_id
                                                           and orden_trabajos_certificaciones.deleted IS NULL GROUP BY orden_trabajos_distribucione_id) certif ON certif.orden_trabajos_distribucione_id = otd.id";
                   

                    /* Le agrego el filtro de establecimiento */
                    if ($establecimiento_id) {
                        $query = $query." AND ot.establecimiento_id = '".$establecimiento_id."' ";
                    }
                    // if ($campania_id) {
                    //     $query = $query." AND pr.campania_monitoreo_id = '".$campania_id."' ";
                    // }
                    $query = $query." ORDER BY ot ASC";
    }

    /**
     * Enviar OT
     * Enviar por mail al proveedor la Orden de Trabajo 
     * 
     * @param type $orden_trabajo_id Id del orden de trabajo
     */
    public function EnviarEmailProveedor($orden_trabajo_id = null)
    {
        if (!$orden_trabajo_id) {
            return $response = ['status' => 'error', 'message' => 'Error, OT no exite !'];
        }

        $ordenTrabajo = $this->get($orden_trabajo_id, [
            'contain' => (['Proveedores' => ['fields' => ['id', 'nombre', 'email']],
                           'Establecimientos' => ['fields' => ['id', 'nombre']],
                           'OrdenTrabajosEstados' => ['fields' => ['id', 'nombre']],
                           'OrdenTrabajosCondicionesMeteorologicas',
                           'Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']],
                           'OrdenTrabajosCertificaciones' => ['Users' => ['finder' => ['all' => ['withDeleted']], 'fields' => ['id', 'nombre']]],
                           'OrdenTrabajosDistribuciones' => ['ProyectosLabores',
                                                             'Lotes' => ['Sectores'], 
                                                             'Unidades',
                                                             'Proyectos',
                                                             'Monedas',
                                                             'OrdenTrabajosCertificaciones',
                                                             'OrdenTrabajosDistribucionesTarifarios'],
                           'OrdenTrabajosInsumos' => ['Productos', 'Unidades', 'Almacenes', 'ProductosLotes']])
        ]);

        $archivo_pdf = $this->GenerarPdfOrdenTrabajo($ordenTrabajo);
        
        $email = new Email();
        $email->template('orden_trabajos_email_proveedores', 'orden_trabajos_email_proveedores')
                ->emailFormat('html')
                ->attachments([
                    'logo.jpg' => [
                        'file' => WWW_ROOT.'img/logoadeco1.jpg',
                        'mimetype' => 'image/jpg',
                        'contentId' => 'logo-adeco'
                    ],
                    'OT_'.$ordenTrabajo->id.'.pdf' => [
                        // 'file' => TMP . 'OT'.$ordenTrabajo->id.'.pdf'    // file => para poner direccion donde se guardo
                        'data' => $archivo_pdf                              // data => para enviar el archivo sin guardar
                    ]
                ])
                ->helpers(['InlineCss.InlineCss'])
                ->setViewVars(['ordenTrabajo' => $ordenTrabajo])
                ->from(['noresponder@elagronomo.com' => 'El Agronomo.com'])
                ->to( $ordenTrabajo->proveedore->email )
                ->subject('Orden de Trabajo Nro.'.$ordenTrabajo->id.'-'.$ordenTrabajo->proveedore->nombre);

        if (!empty($seleccionarArchivos)) {
            $email->attachments($seleccionarArchivos);
        }

        if ($email->send()) {
            return $response = ['status' => 'success', 'message' => 'Email enviado exitosamente.'];
        } else {
            return $response = ['status' => 'error', 'message' => 'Error, email no enviado.'];
        }

    }
    
    /**
     * GenerarPdfOrdenTrabajo
     * Genera el PDF que se enviara adjunto en el email al proveedor
     * @param object | $ordenTrabajo
     * @return data | $pdf 
     *  
     */
    private function GenerarPdfOrdenTrabajo($ordenTrabajo) {
        
        $template = ($ordenTrabajo->orden_trabajos_estado_id == 4) ? 'orden_trabajos_proveedor_certificada' : 'orden_trabajos_proveedor';
        $CakePdf = new \CakePdf\Pdf\CakePdf();
        $CakePdf->template($template,'orden_trabajos_proveedor');
        $CakePdf->viewVars(['ordenTrabajo' => $ordenTrabajo]);

        // Get the PDF string returned
        $pdf = $CakePdf->output();                                       // genera el  PDF
        //$pdf = $CakePdf->write(TMP . 'OT'.$ordenTrabajo->id.'.pdf');   // guardar el PDF en Temporal

        return $pdf;

    }
}