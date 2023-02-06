<?php
/**
 * Muestro el listado de Big Bags de Semillas asociados a la linea de insumos.
 * 
 * @var \App\View\AppView $this
 * @var \App\Model\Entity\OrdenTrabajosInsumo[]|\Cake\Collection\CollectionInterface $ordenTrabajosInsumos
 */
?>
<div class="modal-header text-left">
    <div class="pull-right">
        <?= $this->Form->button('<i class="fa fa-times"></i>', ['class' => 'btn-monitoreo btn-icon-only btn-circle' , 'data-dismiss' => "modal", 'title' => 'Cancelar','type' => 'button', 'escape' => false]) ?>
    </div>
    <h3 class="modal-title">Listado de Big Bags asociados </h3>
</div>
<div class="modal-body">
    <div class="row">
        <div class="col-md-12">
            <table id="tabla-desasociar" cellpadding="0" cellspacing="0" class="table table-bordered table-hover table-striped dataTable">
                <thead>
                    <tr>
                        <th scope="col">Producto</th>
                        <th scope="col">Codigo</th>
                        <th scope="col">Lote</th>
                        <th scope="col">Id Big Bag</th>
                        <th scope="col"> KGs</th>
                        <th scope="col" class="actions"><?= __('Actions') ?></th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($semillas_bolsas as $bolsa): ?>
                    <tr>
                        <td><?= $insumos->producto ? $insumos->producto->nombre : '' ?></td>
                        <td><?= $insumos->producto ? $insumos->producto->codigooracle : '' ?></td>
                        <td><?= $insumos->productos_lote ? $insumos->productos_lote->nombre : '' ?></td>
                        <td><?= $bolsa['id'] ?></td>
                        <td><?= $bolsa['peso'] ?></td>
                        <td class="actions text-center">
                            <?= $this->Form->button('<i class="fa fa-trash"></i>', ['class' => 'btn btn-xs btn-default btn-orden-trabajo DesasociarBB' , 'data-id' => $bolsa['id'], 'title' => 'Desasociar BB','type' => 'button', 'escape' => false]) ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<script>
    /**
     * 
     * Desasociamos el bb de la Orden de Trabajo
     * 
     */
    $(document).on("click", ".DesasociarBB", function(e) {
        e.preventDefault();
        e.stopPropagation();
        var id = e.currentTarget.getAttribute('data-id');

        fetch(`/orden-trabajos-insumos/desasociar/${ id }`, {
            method: 'DELETE'
            })
            .then(res => res.json())
            .then(data => {
                
                if (data.status === 'error') {
                    toastr.error(data.message);
                    return;
                }
                
                toastr.success(data.message);
                $(this).closest('tr').remove();
                
                /* Intento limpiar el id de la subtabla */
                $(".contractors-table-details" + data.insumo_id).DataTable().rows().iterator('row', function (context, index) {
                    let node = $(this.row(index).data());
                    if (node[0].id == data.entrega_id) {
                        /* Tiene el mismo id de entrega que la linea eliminada */
                        $( this.row( index ).node() ).remove();
                    }
                });
            }).catch(err => {
                    console.error(err);
            });
    });
  
</script>