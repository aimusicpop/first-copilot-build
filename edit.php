<?= $this->extend('themes/default/layouts/main') ?>

<?= $this->section('styles') ?>
<link href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.11.0/styles/default.min.css" rel="stylesheet">
<link href="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.9/css/select2.min.css" rel="stylesheet" integrity="sha512-nMNlpuaDPrqlEls3IX/Q56H36qvBASwb3ipuo3MxeWbsQB1881ox0cRv7UPTgBlriqoynt35KjEwgGUeUXIPnw==" crossorigin="anonymous" referrerpolicy="no-referrer" />
<style>
    .select2-container .select2-selection--single {
        height: 38px;
    }

    .select2-container--default .select2-selection--single .select2-selection__rendered {
        line-height: 38px;
    }

    .select2-container--default .select2-selection--single .select2-selection__arrow {
        height: 38px;
    }
</style>
<?= $this->endSection() ?>

<?= $this->section('content') ?>
<div class="container mt-5">
    <h1>Edit Form Configuration for Table: <?= esc($table) ?></h1>
    <div class='card row ' style='margin: 10px;'>
        <div class='card-header col-md-12'>
            <h5>JSON Array</h5>
        </div>
        <div class='card-body col-md-12'>
            <code class="php"><?= json_encode($tables) ?></code>
        </div>
    </div>
    <?php if (session()->getFlashdata('error')): ?>
        <div class="alert alert-danger">
            <?= session()->getFlashdata('error') ?>
            <?php if (isset($missingFields)): ?>
                <br>
                <a href="/forms/addtime/<?= esc($table) ?>" class="btn btn-warning mt-2">Tambahkan Kolom Timestamp</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (session()->getFlashdata('success')): ?>
        <div class="alert alert-success">
            <?= session()->getFlashdata('success') ?>
        </div>
    <?php endif; ?>

    <form action="/forms/update" method="post">
        <input type="hidden" name="<?= csrf_token() ?>" value="<?= csrf_hash() ?>">
        <input type="hidden" name="id" value="<?= esc($formConfig['id'] ?? '') ?>">
        <input type="hidden" name="table" value="<?= esc($table) ?>">
        <div class="row">
            <div class="mb-3 col-md-4">
                <label>Form Title</label>
                <input type="text" name="title" class="form-control" value="<?= esc($formConfig['title'] ?? '') ?>" required>
            </div>
            <div class="mb-3 col-md-4">
                <label>Form Description</label>
                <textarea name="description" class="form-control"><?= esc($formConfig['description'] ?? '') ?></textarea>
            </div>
            <div class="mb-3 col-md-4">
                <label>User-Friendly URL</label>
                <input type="text" name="routes" class="form-control" value="<?= esc($formConfig['routes'] ?? '') ?>" placeholder="Contoh: pegawai" required>
                <small class="text-muted">URL ini akan digunakan untuk routing CRUDL (contoh: /pegawai).</small>
            </div>
        </div>
        <!-- Field Configuration -->
        <div id="fieldsContainer">
            <?php foreach ($fields as $field): ?>
                <?php
                // Skip field created_at, updated_at, dan deleted_at
                if (in_array($field->name, ['created_at', 'updated_at', 'deleted_at'])) {
                    continue;
                }
                ?>
                <div class="mb-3 border p-3 row">
                    <h5>Field: <?= esc($field->name) ?></h5>
                    <div class="mb-3 col-md-3">
                        <label>Field Label</label>
                        <input type="text" name="fields[<?= esc($field->name) ?>][label]" class="form-control" value="<?= esc($formConfig['fields'][$field->name]['label'] ?? ucfirst($field->name)) ?>" required>
                        <input type="hidden" name="fields[<?= esc($field->name) ?>][name]" value="<?= esc($field->name) ?>">
                    </div>
                    <div class="row fields col-md-3">
                        <div class="mb-3 field-type">
                            <label>Field Type</label>
                            <select name="fields[<?= esc($field->name) ?>][type]" class="form-control select2">
                                <option value="text" <?= (isset($formConfig['fields'][$field->name]['type']) && $formConfig['fields'][$field->name]['type'] === 'text') ? 'selected' : '' ?>>Text</option>
                                <option value="textarea" <?= (isset($formConfig['fields'][$field->name]['type']) && $formConfig['fields'][$field->name]['type'] === 'textarea') ? 'selected' : '' ?>>Textarea</option>
                                <option value="select" <?= (isset($formConfig['fields'][$field->name]['type']) && $formConfig['fields'][$field->name]['type'] === 'select') ? 'selected' : '' ?>>Select</option>
                                <option value="checkbox" <?= (isset($formConfig['fields'][$field->name]['type']) && $formConfig['fields'][$field->name]['type'] === 'checkbox') ? 'selected' : '' ?>>Checkbox</option>
                                <option value="radio" <?= (isset($formConfig['fields'][$field->name]['type']) && $formConfig['fields'][$field->name]['type'] === 'radio') ? 'selected' : '' ?>>Radio</option>
                            </select>
                        </div>
                        <!-- Chained Select2 untuk Select -->
                        <div class="mb-3 chained-select" style="display: <?= (isset($formConfig['fields'][$field->name]['type']) && $formConfig['fields'][$field->name]['type'] === 'select') ? 'block' : 'none'; ?>;">
                            <label>Select Option Source</label>
                            <select name="fields[<?= esc($field->name) ?>][select_source]" class="form-control select-source">
                                <option value="manual" <?= (isset($formConfig['fields'][$field->name]['select_source']) && $formConfig['fields'][$field->name]['select_source'] === 'manual' ? 'selected' : '') ?>>Manual</option>
                                <option value="table" <?= (isset($formConfig['fields'][$field->name]['select_source']) && $formConfig['fields'][$field->name]['select_source'] === 'table' ? 'selected' : '') ?>>From Table</option>
                            </select>

                            <!-- Manual Options -->
                            <div class="mb-3 manual-options" style="display: <?= (isset($formConfig['fields'][$field->name]['select_source']) && $formConfig['fields'][$field->name]['select_source'] === 'manual' ? 'block' : 'none') ?>;">
                                <label>Manual Options</label>
                                <textarea name="fields[<?= esc($field->name) ?>][manual_options]" class="form-control" placeholder="Contoh: Male,Male\nFemale,Female"><?= esc($formConfig['fields'][$field->name]['manual_options'] ?? '') ?></textarea>
                            </div>
                            <!-- Table Options -->
                            <div class="mb-3 table-options" style="display: <?= (isset($formConfig['fields'][$field->name]['select_source']) && $formConfig['fields'][$field->name]['select_source'] === 'table' ? 'block' : 'none') ?>;">
                                <label>Select Table</label>
                                <select name="fields[<?= esc($field->name) ?>][table]" class="form-control select-table">
                                    <option value="">Pilih Tabel</option>
                                    <?php if (isset($tables)): ?>
                                        <?php foreach ($tables as $table): ?>
                                            <option value="<?= esc($table) ?>" <?= (isset($formConfig['fields'][$field->name]['table']) && $formConfig['fields'][$field->name]['table'] === $table ? 'selected' : '') ?>><?= esc($table) ?></option>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </select>

                                <div class="mb-3">
                                    <label>Value Field</label>
                                    <select name="fields[<?= esc($field->name) ?>][value_field]" class="form-control value-field" <?= (isset($formConfig['fields'][$field->name]['table']) && $formConfig['fields'][$field->name]['table']) ? '' : 'disabled' ?>>
                                        <option value="">Pilih Field</option>
                                        <?php if (isset($formConfig['fields'][$field->name]['value_field'])): ?>
                                            <option value="<?= esc($formConfig['fields'][$field->name]['value_field']) ?>" selected><?= esc($formConfig['fields'][$field->name]['value_field']) ?></option>
                                        <?php endif; ?>
                                    </select>
                                </div>

                                <div class="mb-3">
                                    <label>Label Field</label>
                                    <select name="fields[<?= esc($field->name) ?>][label_field]" class="form-control label-field" <?= (isset($formConfig['fields'][$field->name]['table']) && $formConfig['fields'][$field->name]['table']) ? '' : 'disabled' ?>>
                                        <option value="">Pilih Field</option>
                                        <?php if (isset($formConfig['fields'][$field->name]['label_field'])): ?>
                                            <option value="<?= esc($formConfig['fields'][$field->name]['label_field']) ?>" selected><?= esc($formConfig['fields'][$field->name]['label_field']) ?></option>
                                        <?php endif; ?>
                                    </select>
                                </div>
                            </div>
                        </div>

                        <div class="mb-3 col-md-3">
                            <label>Show On</label>
                            <div>
                                <input type="checkbox" name="fields[<?= esc($field->name) ?>][show_on][]" value="create" <?= (isset($formConfig['fields'][$field->name]['show_on']) && in_array('create', $formConfig['fields'][$field->name]['show_on']) ? 'checked' : '') ?>> Create
                                <input type="checkbox" name="fields[<?= esc($field->name) ?>][show_on][]" value="edit" <?= (isset($formConfig['fields'][$field->name]['show_on']) && in_array('edit', $formConfig['fields'][$field->name]['show_on']) ? 'checked' : '') ?>> Edit
                                <input type="checkbox" name="fields[<?= esc($field->name) ?>][show_on][]" value="list" <?= (isset($formConfig['fields'][$field->name]['show_on']) && in_array('list', $formConfig['fields'][$field->name]['show_on']) ? 'checked' : '') ?>> List
                                <input type="checkbox" name="fields[<?= esc($field->name) ?>][show_on][]" value="read" <?= (isset($formConfig['fields'][$field->name]['show_on']) && in_array('read', $formConfig['fields'][$field->name]['show_on']) ? 'checked' : '') ?>> Read
                                <input type="checkbox" name="fields[<?= esc($field->name) ?>][show_on][]" value="delete" <?= (isset($formConfig['fields'][$field->name]['show_on']) && in_array('delete', $formConfig['fields'][$field->name]['show_on']) ? 'checked' : '') ?>> Delete
                            </div>
                        </div>
                        <div class="mb-3 col-md-3">
                            <label>Validation Rules</label>
                            <input type="text" name="fields[<?= esc($field->name) ?>][validation_rule]" class="form-control" value="<?= esc($formConfig['fields'][$field->name]['validation_rule'] ?? '') ?>" placeholder="Contoh: required|numeric|min_length[5]">
                        </div>
                        <div class="mb-3">
                            <input type="checkbox" name="fields[<?= esc($field->name) ?>][required]" value="1" <?= (isset($formConfig['fields'][$field->name]['required']) && $formConfig['fields'][$field->name]['required'] == 1 ? 'checked' : '') ?>> Required
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>

                <button type="submit" class="btn btn-primary">Update Form</button>
                
    </form>
</div>
<?= $this->endSection() ?>

<?= $this->section('scripts') ?>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.11.0/highlight.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.11.1/languages/php.min.js" integrity="sha512-uSKj9vayq7XKzfzflBQdmCuLIzKtsmsv7jjfr85Z0GQxNyID1anc0GMYHsNMo93A0oaro6696CQ5Q00xvCpoBQ==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.11.1/languages/json.min.js" integrity="sha512-f2/ljYb/tG4fTHu6672tyNdoyhTIpt4N1bGrBE8ZjwIgrjDCd+rljLpWCZ2Vym9PBWQy2Tl9O22Pp2rMOMvH4g==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>

<script src="https://cdnjs.cloudflare.com/ajax/libs/select2/4.0.9/js/select2.min.js" integrity="sha512-9p/L4acAjbjIaaGXmZf0Q2bV42HetlCLbv8EP0z3rLbQED2TAFUlDvAezy7kumYqg5T8jHtDdlm1fgIsr5QzKg==" crossorigin="anonymous" referrerpolicy="no-referrer"></script>
<script>
    hljs.highlightAll();
    $(document).ready(function() {
        <?php if (session()->getFlashdata('success')): ?>
            toastr.success('<?= session()->getFlashdata('success') ?>');
        <?php endif; ?>

        <?php if (session()->getFlashdata('error')): ?>
            toastr.error('<?= session()->getFlashdata('error') ?>');
        <?php endif; ?>

        $('.select2').select2();
        $('.select-table').select2();
        $('.value-field').select2();
        $('.label-field').select2();

        $('select[name*="[type]"]').change(function() {
            const fieldType = $(this).val();
            const chainedSelect = $(this).closest('.field-type').next('.chained-select');

            if (fieldType === 'select') {
                chainedSelect.show();
            } else {
                chainedSelect.hide();
            }
        });

        $('.select-source').change(function() {
            const selectSource = $(this).val();
            const manualOptions = $(this).closest('.chained-select').next('.manual-options');
            const tableOptions = $(this).closest('.chained-select').nextAll('.table-options').first();

            if (selectSource === 'manual') {
                manualOptions.show();
                tableOptions.hide();
            } else if (selectSource === 'table') {
                manualOptions.hide();
                tableOptions.show();
            } else {
                manualOptions.hide();
                tableOptions.hide();
            }
        });

        $('.select-table').change(function() {
            const table = $(this).val();
            const valueField = $(this).closest('.table-options').find('.value-field');
            const labelField = $(this).closest('.table-options').find('.label-field');

            if (table) {
                $.ajax({
                    url: '/forms/getfields',
                    method: 'POST',
                    data: {
                        table: table
                    },
                    success: function(response) {
                        try {
                            const fields = JSON.parse(response);
                            if (fields.error) {
                                alert(fields.error);
                                return;
                            }
                            let valueOptions = '<option value="">Pilih Field</option>';
                            let labelOptions = '<option value="">Pilih Field</option>';

                            fields.forEach(field => {
                                valueOptions += `<option value="${field.name}">${field.name}</option>`;
                                labelOptions += `<option value="${field.name}">${field.name}</option>`;
                            });

                            valueField.html(valueOptions).prop('disabled', false);
                            labelField.html(labelOptions).prop('disabled', false);
                        } catch (error) {
                            console.error('Error parsing JSON:', error);
                            alert('Gagal memproses data dari server.');
                        }
                    },
                    error: function() {
                        alert('Gagal mengambil field dari tabel.');
                    }
                });
            } else {
                valueField.html('<option value="">Pilih Field</option>').prop('disabled', true);
                labelField.html('<option value="">Pilih Field</option>').prop('disabled', true);
            }
        });
    });
</script>
<?= $this->endSection() ?>