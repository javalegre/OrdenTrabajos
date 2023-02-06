<?php
/**
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\OrdenTrabajo[]|\Cake\Collection\CollectionInterface $ordenTrabajos
 */
?>
<div class="row border-bottom white-bg page-heading">
    <div class="col-md-7">
        <h2>Exportación de Datos</h2>
        <small>Este módulo genera un archivo para ser utilizado con el dataload.</small>
    </div>
    <div class="col-md-5 m-t-md text-right">
        <h4><?= $ordenTrabajos->count() ?> Ordenes de Trabajo.</h4>
        <span class="small">serán incluidos en esta exportación.</span>
    </div>
</div>

<div class="row m-r-n-sm">
    <div class="col-lg-12">
        <div class="ibox">
            <div class="ibox-content">
                <div class="row">
                    <?= $this->Form->button('Generar Dataload', ['type' => 'button', 'class' => 'btn btn-w-m btn-success' ,'id' => 'dataload']) ?>
                </div>
                <br>
                <table class="table table-bordered table-hover table-striped dataTable sin-margen-superior" id='tabla-dataload'>
                    <thead><?= $this->Html->tableHeaders(['OT','Fecha', 'Establecimiento', 'Proveedor','Lotes','Estado','']) ?></thead>
                    <tbody>
                        <?php foreach ($ordenTrabajos as $ordenTrabajo): ?>
                        <tr>
                            <td><?= h($ordenTrabajo->id) ?></td>
                            <td><?= h(date_format($ordenTrabajo->fecha,'d/m/Y')) ?></td>
                            <td><?= h($ordenTrabajo->establecimiento['nombre']) ?></td>
                            <td><?= h($ordenTrabajo->proveedore['nombre']) ?></td>
                            <td>
                                <?php
                                    foreach ($ordenTrabajo->orden_trabajos_distribuciones as $distribucion){
                                        echo '<span class="badge badge-success">Lote: ' .$distribucion['lote']['nombre'].' <span class="small">  ('.$distribucion['superficie'].' has)'.'</span></span>';
                                    }
                                ?>
                            </td>
                            <td>
                                <?php
                                    switch ($ordenTrabajo->orden_trabajos_estado['id']){
                                        case 1: /* Borrador */
                                            //echo '<a class="btn btn-block btn-warning">'.$ordenTrabajo->orden_trabajos_estado['nombre'].'</a>';
                                            echo $this->Html->link($ordenTrabajo->orden_trabajos_estado['nombre'], ['action' => 'edit', $ordenTrabajo->id],['type'=>'button','class'=>'btn btn-block btn-warning','escape' => false]);
                                            break;
                                        case 2: /* Pendiente de Aprobacion */
                                            break;
                                        case 3: /* Aprobado, muestro certificar */
                                            echo $this->Html->link($ordenTrabajo->orden_trabajos_estado['nombre'], ['action' => 'edit', $ordenTrabajo->id],['type'=>'button','class'=>'btn btn-block btn-primary','escape' => false]);                                            
                                            break;
                                        case 4:
                                            echo $this->Html->link($ordenTrabajo->orden_trabajos_estado['nombre'], ['action' => 'edit', $ordenTrabajo->id],['type'=>'button','class'=>'btn btn-block btn-success','escape' => false]);
                                            break;
                                    }
                                ?>
                            </td>
                            <td class="cell-double-action">
                                <div class="btn-group">
                                    <?= $this->Form->button('<i class="fa fa-times"></i>', ['type' => 'button', 'class' => 'btn btn-xs btn-danger eliminar' ,'id' => 'dataload', 'escape' => false]) ?>
                                </div>                                
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>                
            </div>
        </div>
    </div>
</div>

<script>
    $(document).ready(function() {
        var table = $('#tabla-dataload').DataTable({
            pageLength: 10,
            destroy: true,
            deferRender: false,
            dom:  "<'row'<'col-sm-12 no-margin no-padding'tr>>"
        });  

        $('#tabla-dataload tbody').on( 'click', '.eliminar', function () {
            var linea =  $(this).parents('tr');
            table.row(linea).remove().draw( false );
        } );        
        
        /* Armo todas las fechas */
        $( "#dataload" ).click(function() {
            var valores = [];
  
            //Obtengo los valores del grid sin elemento html
            var rows = $("#tabla-dataload").dataTable().fnGetData();
            
            /* Ahora armo un array con los ID's de las OT que serán incluidas */
            $(rows).each(function() {
                valores.push($(this)[0]);
            });
            
            /* Tengo ya el array con las OT a generar el excel */
            $.ajax({
                type:"POST", 
                async:true,
                data: {valores},
                url:"/orden-trabajos-dataloads/generardataload",    /* Pagina que procesa la peticion   */
                success:function (data){
                    console.log(data);
                    //resultado = JSON.parse(data);
                },
                error: function (data) {
                    console.log(data);
                    //alert('error' + data.statusText);
                    //resultado = JSON.parse(data);
                }
            });
        });
    });     
</script>