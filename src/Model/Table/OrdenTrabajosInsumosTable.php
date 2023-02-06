<?php
namespace Ordenes\Model\Table;

use Cake\ORM\Query;
use Cake\ORM\RulesChecker;
use Cake\ORM\Table;
use Cake\Validation\Validator;
use SoftDelete\Model\Table\SoftDeleteTrait;
use Cake\ORM\TableRegistry;
use Cake\Datasource\ConnectionManager;
use Cake\Mailer\Email;
use Firebase\JWT\JWT;
use Cake\Utility\Security;
use Cake\I18n\Time;

/**
 * OrdenTrabajosInsumos Model
 *
 * @property \App\Model\Table\OrdenTrabajosTable|\Cake\ORM\Association\BelongsTo $OrdenTrabajos
 * @property \App\Model\Table\ProductosTable|\Cake\ORM\Association\BelongsTo $Productos
 * @property \App\Model\Table\OrdenTrabajosDistribucionesTable|\Cake\ORM\Association\BelongsTo $OrdenTrabajosDistribuciones
 * @property \App\Model\Table\UnidadesTable|\Cake\ORM\Association\BelongsTo $Unidades
 * @property \App\Model\Table\AlmacenesTable|\Cake\ORM\Association\BelongsTo $Almacenes
 * @property \App\Model\Table\OrdenTrabajosInsumosDevolucionesTable|\Cake\ORM\Association\HasMany $OrdenTrabajosInsumosDevoluciones
 * @property \App\Model\Table\OrdenTrabajosInsumosEntregasTable|\Cake\ORM\Association\HasMany $OrdenTrabajosInsumosEntregas
 *
 * @method \App\Model\Entity\OrdenTrabajosInsumo get($primaryKey, $options = [])
 * @method \App\Model\Entity\OrdenTrabajosInsumo newEntity($data = null, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosInsumo[] newEntities(array $data, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosInsumo|bool save(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OrdenTrabajosInsumo|bool saveOrFail(\Cake\Datasource\EntityInterface $entity, $options = [])
 * @method \App\Model\Entity\OrdenTrabajosInsumo patchEntity(\Cake\Datasource\EntityInterface $entity, array $data, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosInsumo[] patchEntities($entities, array $data, array $options = [])
 * @method \App\Model\Entity\OrdenTrabajosInsumo findOrCreate($search, callable $callback = null, $options = [])
 *
 * @mixin \Cake\ORM\Behavior\TimestampBehavior
 */
class OrdenTrabajosInsumosTable extends Table
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
                
        $this->setTable('orden_trabajos_insumos');
        $this->setDisplayField('id');
        $this->setPrimaryKey('id');

        $this->addBehavior('Timestamp');

        $this->belongsTo('Ordenes.OrdenTrabajos', [
            'foreignKey' => 'orden_trabajo_id'
        ]);
        $this->belongsTo('Ordenes.OrdenTrabajosDistribuciones', [
            'foreignKey' => 'orden_trabajos_distribucione_id'
        ]);
        $this->hasMany('Ordenes.OrdenTrabajosInsumosDevoluciones', [
            'foreignKey' => 'orden_trabajos_insumo_id'
        ]);
        $this->hasMany('Ordenes.OrdenTrabajosInsumosEntregas', [
            'foreignKey' => 'orden_trabajos_insumo_id'
        ]);
        $this->hasOne('Ordenes.OrdenTrabajosInsumosCostos', [
            'foreignKey' => 'orden_trabajos_insumo_id'
        ]);
        $this->belongsTo('Productos', [
            'foreignKey' => 'producto_id'
        ]);
        $this->belongsTo('ProductosLotes', [
            'foreignKey' => 'productos_lote_id'
        ]);
        
        $this->belongsTo('Unidades', [
            'foreignKey' => 'unidade_id'
        ]);
        $this->belongsTo('Almacenes', [
            'foreignKey' => 'almacene_id'
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
            ->numeric('dosis')
            ->requirePresence('dosis', 'create')
            ->notEmpty('dosis');

        $validator
            ->numeric('cantidad')
            ->requirePresence('cantidad', 'create')
            ->notEmpty('cantidad');

        $validator
            ->numeric('cantidad_stock')
            ->requirePresence('cantidad_stock', 'create')
            ->notEmpty('cantidad_stock');

        $validator
            ->numeric('utilizado')
            ->allowEmpty('utilizado');

        $validator
            ->numeric('dosis_aplicada')
            ->allowEmpty('dosis_aplicada');
        
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
        $rules->add($rules->existsIn(['producto_id'], 'Productos'));
        //$rules->add($rules->existsIn(['orden_trabajos_distribucione_id'], 'OrdenTrabajosDistribuciones'));
        $rules->add($rules->existsIn(['unidade_id'], 'Unidades'));
        $rules->add($rules->existsIn(['almacene_id'], 'Almacenes'));

        return $rules;
    }
    /**
     * Devuelvo la cantidad de Insumos Entregados
     * 
     */
    public function findEntregas(Query $query, $options = [])
    {
        $query = $this->OrdenTrabajosInsumosEntregas->find('all')
            ->where(['OrdenTrabajosInsumosEntregas.orden_trabajos_insumo_id' => $options['IdInsumos']])
            ->select(['entregas' => $query->func()->sum('cantidad')])
            ->first();
        
        return $query;
    }     
   /**
     * Devuelvo la cantidad de Insumos devueltos
     * 
     */
    public function findDevoluciones(Query $query, $options = [])
    {
        $query = $this->OrdenTrabajosInsumosDevoluciones->find('all')
            ->select(['devoluciones' => $query->func()->sum('cantidad')])
            ->where(['OrdenTrabajosInsumosDevoluciones.orden_trabajos_insumo_id' => $options['IdInsumos']])
            ->first();

        return $query;
    }
    
    /**
     * Creo un registro que anula el movimiento solicitado y los marca como eliminados
     * Tanto entregas como devoluciones.
     * 
     */
    public function EliminarInsumo ($id) {
        
        $insumo = $this->get($id);
        
        /* Anulo todas las entregas */
        $this->eliminarEntregas($id);

        /* Anulo todas las entregas */
        $this->eliminarDevoluciones($id);        
        
        $respuesta = $this->delete($insumo);
        
        return true;
    }
    
    /**
     * Anulo todas las devoluciones
     * 
     * @param type $id OrdenTrabajosInsumos
     * @return boolean
     */
    private function eliminarDevoluciones ($id = null) {

        $tabla = TableRegistry::getTableLocator()->get('OrdenTrabajosInsumosDevoluciones');
        
        $insumo = $this->get($id);
        
        $devoluciones = $tabla->find('all', [
            'conditions' => ['orden_trabajos_insumo_id' => $id,
                             'producto_id' => $insumo->producto_id]
        ]);
        foreach ($devoluciones as $devolucion) {
            /* Marco la devolucion como ya subido a la interfaz */
            $devolucion->oracle_flag = 'Y';
            $devolucion->interface_error = 'El Agronomo - Siembra';
            $tabla->save($devolucion);
            
            /* Ahora hago el movimiento reversivo */
            $anula = $devolucion;
            unset ($anula->id);
            unset ($anula->oracle_flag);
            unset ($anula->interface_error);
            
            $anula->cantidad = -1 * abs($devolucion->cantidad);
            $anula->isNew(true);
            
            $tabla->save($anula);
        }
        /* Ahora que estan anulados, borro todos los registros */
        $tabla->deleteAll(['orden_trabajos_insumo_id' => $id, 'producto_id' => $insumo->producto_id]);
        
        return true;
    }

    /**
     * Anulo todas las entregas
     * 
     * @param type $id OrdenTrabajosInsumos
     * @return boolean
     */
    private function eliminarEntregas ($id = null) {
        $tabla = TableRegistry::getTableLocator()->get('OrdenTrabajosInsumosEntregas');
        
        $insumo = $this->get($id);
        
        $entregas = $tabla->find('all', [
            'conditions' => ['orden_trabajos_insumo_id' => $id,
                             'producto_id' => $insumo->producto_id]
        ]);
        foreach ($entregas as $entrega) {
            /* Marco la entrega como ya subido a la interfaz */
            $entrega->oracle_flag = 'Y';
            $entrega->interface_error = 'El Agronomo - Siembra';
            $tabla->save($entrega);
            
//            $anula = $entrega;
//            unset ($anula->id);
//            /* unset ($anula->oracle_flag);
//            unset ($anula->interface_error); */
//            
//            $anula->cantidad = -1 * abs($entrega->cantidad);
//            $anula->isNew(true);
//            
//            $tabla->save($anula);
        }
        /* Ahora que estan anulados, borro todos los registros */
        $tabla->deleteAll(['orden_trabajos_insumo_id' => $id, 'producto_id' => $insumo->producto_id]);
        return true;
    }
    
    /**
     * Cambia los productos de una OT, llevando un control de auditoría de los cambios efectuados.
     * 
     * @param type $id OrdenTrabajosInsumos
     * @param type $producto array Producto que se cambió, contiene el producto y el motivo del cambio.
     * @return boolean
     */
    public function cambiarProductos ($id = null, $producto = null, $user_id = null) {
        
        $status = [
            'status' => '',
            'message' => ''
        ];
        
        /* El primer cambio es guardar la modificacion en la tabla */
        $insumo = $this->get($id, [
            'contain' => [
                            'OrdenTrabajos',
                            'Productos' => ['fields' => ['id', 'nombre']]
                         ]
        ]);
        
        /* Si la OT está certificada, tengo que avisar al ingeniero y guardar en auditoria */
        if ($insumo->orden_trabajo->orden_trabajos_estado_id == 4) {
            
            /* 1 - Guardar el pedido de cambio en la table de auditorias */
            $tokenize = $this->cambiarProductosAuditoria ($insumo, $producto, $user_id);
            
            /* 2 - Enviar el pedido de aprobacion por mail a los usuarios aprobadores */
            $this->notificarAprobadores($insumo, $tokenize);
            
            $status['status'] = 'success';
            $status['message'] = 'Se solicitó el cambio de insumo para su aprobación.';
        } else {
            
            $insumo = $this->get($id);
            
            $insumo->producto_id = $producto['valor'];
            
            $this->save( $insumo );

            $status['status'] = 'success';
            $status['message'] = 'Se cambió el insumo correctamente.';
        }
        
        return $status;
    }
    
    /**
     * Busca los cambios solicitados y los ejecuta en caso de que estén aprobados.
     * 
     * @param type $token Token
     * @return boolean
     */
    public function aplicarCambioProductos ($token = null, $user_id = null) {
        
        $tabla = TableRegistry::getTableLocator()->get('OrdenTrabajosAuditorias');

        /* 1- Obtener el movimiento solicitado, para eso averiguo la linea x el token */
        $auditorias = $tabla->findByToken($token)->first();        
        
        /* Lo marco como aprobado */
        $auditoria = $tabla->get($auditorias->id);
        $auditoria->aprobador = $user_id;
        $auditoria->status = 1;
        $tabla->save($auditoria);
        
        $linea_insumos = $auditorias->registro_original;
        
        
        
        die(debug( $linea_insumos ));
        
        die(debug( $token ));
        
        
        
        
        
        
        
    }
    
    public function rechazarCambioProductos ($token = null, $user_id = null) {
        /* 1- Obtener el movimiento solicitado, para eso averiguo la linea x el token */
        $tabla = TableRegistry::getTableLocator()->get('OrdenTrabajosAuditorias');
        
        $auditorias = $tabla->find('all', [
            'conditions' => ['token' => $token]
        ])->first();
        
        /* No se encuentra el pedido de cambio solicitado - Posible manipulacion del token */
        if (!$auditorias) {
            return $status = [
                    'status' => 'error',
                    'mensaje' => 'No se encontró ninguna solicitud de cambio.'
                ];
        }
        
        $auditoria = $tabla->get($auditorias->id);
        
        /* Verifico si ya se encuentra aprobada */
        if ($auditoria->aprobador) {
            return $status = [
                    'status' => 'error',
                    'mensaje' => 'La solicitud ya fué aprobada.'
                ];
        }
        
        $status = [
            'status' => 'rechazado',
            'registro' => $auditorias->id,
            'usuario' => $this->OrdenTrabajos->Users->get($user_id)->nombre,
            'fecha' => Time::now()
        ];
        
        $auditoria->status = '2';
        $auditoria->aprobador = json_decode(json_encode($status));
        
        /* Guardo el rechazo */
        if ($tabla->save($auditoria)) {
            /* Notifico el rechazo al usuario */
            $mail_usuario = $this->OrdenTrabajos->Users->get($auditoria->user_id)->email;
            $producto = $this->Productos->get($auditoria->valor)->nombre;
            $observaciones = $auditoria->observaciones;

            $email = new Email();
            $email->template('auditoria_productos_rechazo')
                  ->transport('gmail')
                  ->emailFormat('html')
                  ->attachments([
                        'photo.png' => [
                            'file' => WWW_ROOT.'img/logo_mail_auditoria.png',
                            'mimetype' => 'image/png',
                            'contentId' => 'logo-adeco'
                        ]
                    ])
                  ->helpers(['InlineCss.InlineCss'])
                  ->setViewVars(['insumo' => $auditoria->registro_original,
                                 'token' => $token,
                                 'status' => $status,
                                 'producto' => $producto,
                                 'observaciones' => $observaciones
                          ])
                  ->from(['noresponder@elagronomo.com' => 'Auditoria El Agronomo'])
                  ->to( $mail_usuario )
                  ->subject('Informes de Auditoria El Agronomo - Rechazo');
            $email->send(); 
        } else {
            $status = [
                'status' => 'error',
                'mensaje' => 'Ocurrió un error al intentar guardar el registro.'
            ];
        }
        
        return $status;
    }

    /**
     * Guardo los registros de los cambios para auditoria
     * 
     * @param type $insumo Linea a modificar
     * @param type array   Array con el valor y observacion
     * @param type int     Id de Usuario que realiza la operación
     * @return boolean
     */
    private function cambiarProductosAuditoria ($insumo = null, $producto = null, $user_id = null) {
        
        $auditorias = TableRegistry::getTableLocator()->get('OrdenTrabajosAuditorias');
        $auditoria = $auditorias->newEntity();
        
        $auditoria->orden_trabajos_auditorias_tipo_id = 1;
        $auditoria->registro_original = json_decode(json_encode($insumo));
        $auditoria->valor = $producto['valor'];
        $auditoria->observaciones = $producto['observaciones'];
        $auditoria->establecimiento_id = $insumo->orden_trabajo->establecimiento_id;
        $auditoria->orden_trabajo_id = $insumo->orden_trabajo_id;
        $auditoria->user_id = $user_id;
        
        $token = JWT::encode(
                    [
                        'registro' => $auditoria->registro_original,
                        'valor' =>  $producto['valor']
                    ],
                    Security::salt()
                );
        
        $auditoria->token = $token;
        $auditorias->save( $auditoria );
        
        $producto['nombre'] = $this->Productos->get($producto['valor'])->nombre;
        $producto['usuario'] = $this->OrdenTrabajos->Users->get($user_id)->nombre;
        
        $mensaje = [
            'token' => $auditoria->token,
            'insumo' => $insumo,
            'producto' => $producto,
        ];
        
        return $mensaje;
    }
    
    /**
     * Envio los mails de notificaciones a los auditores configurados para el establecimiento
     * 
     * @param type $establecimiento Establecimiento
     * @return boolean
     */    
    private function notificarAprobadores ($insumo = null, $token = null) {
        
        $establecimiento = $insumo->orden_trabajo->establecimiento_id;

        $destinatarios = $this->BuscarAprobadores($establecimiento);
        
        
        if (!$destinatarios) {
            /* No hay aprobadores definidos para este establecimiento */
            return;
        }
        
        /* 
         * Envio un mail de aprobación a cada aprobador
         * Lo hago de forma individual para poder trackear quien lo aprobó
         * 
         */
        foreach ($destinatarios as $destino) {
            $email = new Email();
            $email->template('auditoria_productos')
                  ->transport('gmail')
                  ->emailFormat('html')
                  ->attachments([
                        'photo.png' => [
                            'file' => WWW_ROOT.'img/logo_mail_auditoria.png',
                            'mimetype' => 'image/png',
                            'contentId' => 'logo-adeco'
                        ]
                    ])
                  ->helpers(['InlineCss.InlineCss'])
                  ->setViewVars(['insumo' => $insumo,
                                 'token' => $token,
                                 'usuario' => $destino
                          ])
                  ->from(['noresponder@elagronomo.com' => 'Auditoria El Agronomo'])
                  ->to( $destino->email )
                  ->subject('Informes de Auditoria El Agronomo');
            $email->send();            
        }
        
        return true;
    }
    
    /**
     * Busco los aprobadores configurados para el establecimiento seleccionado
     * 
     * @param type array $establecimientos
     * @return array
     */
    private function BuscarAprobadores ($establecimiento = null) {
        $mis_aprobadores = [];
        $connection = ConnectionManager::get('default');
        $query = $connection->execute("SELECT * FROM `orden_trabajos_auditorias_configuraciones` WHERE
                                      JSON_SEARCH(`establecimientos`, 'all', '".$establecimiento."', NULL, '$[*]') IS NOT NULL AND deleted IS NULL")->fetchAll('assoc');
        foreach ($query as $row) {
            $aprobadores = json_decode($row['aprobadores']);
            if ($aprobadores) {
                foreach ($aprobadores as $aprobador) {
                    if (!in_array($aprobador, $mis_aprobadores)) {
                        array_push($mis_aprobadores, $aprobador);
                    }
                }
            }
        }
        return $this->BuscarMailsAprobadores($mis_aprobadores);
    }    
    
    /**
     * Busco los mails de los aprobadores configurados para el establecimiento seleccionado
     * 
     * @param type array $aprobadores
     * @return array
     */
    private function BuscarMailsAprobadores ($aprobadores = null) {
        $mis_aprobadores = [];
        $tabla = TableRegistry::getTableLocator()->get('Users');
        if ($aprobadores) {
            $mails = $tabla->find('all', [
                'fields' => ['id','email'],
                'conditions' => ['id IN' => $aprobadores]
            ]);
            $mis_aprobadores = $mails->toArray();
        }
        return $mis_aprobadores;
    }
}
    