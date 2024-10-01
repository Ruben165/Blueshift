<table class="table table-hover" id='fakturTable'>
    <thead>
      <tr>
        <th scope="col" width="25">No</th>
        <th scope="col">No Faktur</th>
        <th scope="col">Tanggal Faktur</th>
        <th scope="col">Aksi</th>
      </tr>
    </thead>
    <tbody>
    </tbody>
    <tfoot>
      <tr>
        <td colspan="4">
          <button class="btn btn-info mb-3" id="addFaktur" type="button">
            <span class="fas fa-plus mr-2"></span>Tambah
          </button>
        </td>
      </tr>
    </tfoot>
</table>

<div class="modal hide fade" tabindex="-1" id="modalAddFaktur">
  <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
          <div class="modal-header">
              <h5 class="modal-title">Tambah Faktur</h5>
              <button type="button" class="close" data-dismiss="modal" aria-label="Close">
              <span aria-hidden="true">&times;</span>
              </button>
          </div>
          <div class="modal-body">
            <div class="form-group">
              <label for="noFaktur">Nomor Faktur</label>
              <input type="text" name="noFaktur" id="noFaktur" class="form-control">
            </div>
            <div class="form-group">
              <label for="dateFaktur">Tanggal Faktur</label>
              <input type="date" name="dateFaktur" id="dateFaktur" class="form-control">
            </div>
          </div>
          <div class="modal-footer">
            <button type="button" class="btn btn-primary" id="addFakturButton">Tambah</button>
          </div>
      </div>
  </div>
</div>