<!-- https://grok.com/chat/0b4d76df-e29f-45a1-bf62-edbafa0000af
- Secara interface sudah baik  
- namun belum maksimal untuk penambahan add rule pada validation rule di modal belum bisa ditambahkan dan ditampilkan dengan baik
- secara select juga belum bisa ditampilkan
-->
<?= $this->extend('themes/default/layouts/main') ?>
<?= $this->section('styles') ?>
<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/css/select2.min.css">
<style>
    .select2-container { width: 100% !important; }
    .field-item { cursor: move; }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container mt-5">
    <h1>Configure Form for Table: <?= esc($table) ?></h1>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger"><?= session()->getFlashdata('error') ?></div>
    <?php endif; ?>
    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success"><?= session()->getFlashdata('success') ?></div>
    <?php endif; ?>

    <form action="/forms/save" method="post" id="configureForm">
        <input type="hidden" name="table" value="<?= esc($table) ?>">
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="form-group">
                    <label>Form Title</label>
                    <input type="text" name="title" class="form-control" value="<?= isset($formConfig) ? esc($formConfig['title']) : '' ?>" required>
                </div>
            </div>
            <div class="col-md-6">
                <div class="form-group">
                    <label>Form Description</label>
                    <textarea name="description" class="form-control"><?= isset($formConfig) ? esc($formConfig['description']) : '' ?></textarea>
                </div>
            </div>
        </div>
        <div class="row mb-4">
            <div class="col-md-6">
                <div class="form-group">
                    <label>User-Friendly URL</label>
                    <input type="text" name="routes" class="form-control" placeholder="Contoh: pegawai" value="<?= isset($formConfig) ? esc($formConfig['routes']) : '' ?>" required>
                    <small class="text-muted">URL ini akan digunakan untuk routing CRUD (contoh: /pegawai).</small>
                </div>
            </div>
        </div>

        <!-- Field Configuration -->
        <div id="fieldsContainer" class="sortable">
            <?php foreach ($fields as $field): ?>
                <?php if (in_array($field->name, ['created_at', 'updated_at', 'deleted_at'])) continue; ?>
                <div class="card mb-3 field-item" data-field-name="<?= esc($field->name) ?>">
                    <div class="card-header">
                        <h5 class="d-inline">Field: <?= esc($field->name) ?></h5>
                        <button type="button" class="btn btn-link float-end" data-bs-toggle="collapse" data-bs-target="#collapse-<?= esc($field->name) ?>">
                            <i class="fas fa-chevron-down"></i>
                        </button>
                    </div>
                    <div id="collapse-<?= esc($field->name) ?>" class="collapse show">
                        <div class="card-body">
                            <div class="row">
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Field Label</label>
                                        <input type="text" name="fields[<?= esc($field->name) ?>][label]" class="form-control" value="<?= ucfirst($field->name) ?>" required>
                                        <input type="hidden" name="fields[<?= esc($field->name) ?>][name]" value="<?= esc($field->name) ?>">
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Field Type</label>
                                        <select name="fields[<?= esc($field->name) ?>][type]" class="form-control select2 field-type">
                                            <option value="text">Text</option>
                                            <option value="textarea">Textarea</option>
                                            <option value="select">Select</option>
                                            <option value="checkbox">Checkbox</option>
                                            <option value="radio">Radio</option>
                                            <option value="date">Date</option>
                                            <option value="daterange">Date Range</option>
                                            <option value="file">File Upload</option>
                                            <option value="number">Number</option>
                                            <option value="email">Email</option>
                                            <option value="password">Password</option>
                                            <option value="toggle">Toggle</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <div class="form-group">
                                        <label>Show On</label>
                                        <div>
                                            <div class="form-check form-switch">
                                                <input type="checkbox" name="fields[<?= esc($field->name) ?>][show_on][]" value="create" class="form-check-input" checked>
                                                <label>Create</label>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input type="checkbox" name="fields[<?= esc($field->name) ?>][show_on][]" value="edit" class="form-check-input" checked>
                                                <label>Edit</label>
                                            </div>
                                            <div class="form-check form-switch">
                                                <input type="checkbox" name="fields[<?= esc($field->name) ?>][show_on][]" value="list" class="form-check-input" checked>
                                                <label>List</label>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="col-md-3">
                                    <button type="button" class="btn btn-sm btn-info mt-4" data-bs-toggle="modal" data-bs-target="#validationModal" data-field="<?= esc($field->name) ?>">
                                        Add Validation
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>

        <button type="submit" class="btn btn-primary">Save Configuration</button>
    </form>
</div>

<!-- Validation Modal -->
<div class="modal fade" id="validationModal" tabindex="-1" aria-labelledby="validationModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="validationModalLabel">Add Validation Rules</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="currentField" name="currentField">
                <div class="form-group">
                    <label>Validation Rules</label>
                    <select class="form-control select2" multiple name="validationRules[]" id="validationRules">
                        <option value="required">Required</option>
                        <option value="min_length[3]">Min Length (3)</option>
                        <option value="max_length[255]">Max Length (255)</option>
                        <option value="numeric">Numeric</option>
                        <option value="alpha">Alpha</option>
                        <option value="alpha_numeric">Alpha Numeric</option>
                        <option value="valid_email">Valid Email</option>
                        <option value="is_unique[<?= esc($table) ?>.<?= esc($field->name) ?>]">Unique</option>
                    </select>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
                <button type="button" class="btn btn-primary" id="saveValidation">Save</button>
            </div>
        </div>
    </div>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.13/js/select2.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/sortablejs@1.15.0/Sortable.min.js"></script>
<script>
    $(document).ready(function() {
        // Inisialisasi Select2
        $('.select2').select2();

        // Inisialisasi SortableJS untuk drag-and-drop
        new Sortable(document.getElementById('fieldsContainer'), {
            animation: 150,
            handle: '.card-header',
            onEnd: function(evt) {
                console.log('Field order changed:', evt.oldIndex, 'to', evt.newIndex);
            }
        });

        // Modal Validation
        $('[data-bs-target="#validationModal"]').on('click', function() {
            const fieldName = $(this).data('field');
            $('#currentField').val(fieldName);
        });

        $('#saveValidation').on('click', function() {
            const fieldName = $('#currentField').val();
            const rules = $('#validationRules').val();
            if (rules) {
                $('<input>').attr({
                    type: 'hidden',
                    name: `fields[${fieldName}][validation]`,
                    value: rules.join('|')
                }).appendTo('#configureForm');
            }
            $('#validationModal').modal('hide');
        });
    });
</script>
<?= $this->endSection() ?>