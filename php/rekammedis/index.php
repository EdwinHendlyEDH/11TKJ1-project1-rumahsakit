<?php 
$GLOBALS['title'] = 'EHealt | Tabel Rekam Medis';
require_once "../functions.php";

EnsureUserAuth($conn, "php/rekammedis");

$current_table = 'tb_rekammedis';


$tbRelation = $tableRelations[$current_table] ?? false;

if($tbRelation){
    [$tbsToRelate, $tbsFieldToGet] = [array_keys($tbRelation), array_values($tbRelation)];
    [$tbId, $tbFields, $tbForeign] = getTableFields($current_table);

    $tbForeign = array_flip($tbForeign);

    // $queryStr = "SELECT tbrm.*, 
    // tbp.`nama_pasien`, 
    // tbd.`nama_dokter`, 
    // tbpk.`nama_poliklinik`
    // FROM `$current_table` tbrm 
    // INNER JOIN `tb_pasien` tbp ON tbrm.`id_pasien` = tbp.`id` 
    // INNER JOIN `tb_dokter` tbd ON tbrm.`id_dokter` = tbd.`id`
    // INNER JOIN `tb_poliklinik` tbpk ON tbrm.`id_poliklinik` = tbpk.`id`";

    $queryStr = "SELECT `$current_table`.*, ";
    $tbSelectionStr = "";
    $tbJoinStr = "";

    foreach($tbsToRelate as $tIdx => $tableRelate) {
        [$tbIdToRelate] = getTableFields($tableRelate);
        foreach($tbsFieldToGet[$tIdx] as $fIdx => $fieldToGet){
            // selection field name and 'as'
            $fieldName = $fieldToGet;
            if(str_contains($fieldName, ':')){
                [$fieldName, $fieldNameAs] = explode(':', $fieldToGet);
            }else{
                $fieldNameAs = $fieldName;
            }
            $tbSelectionStr .= "`$tableRelate`.`$fieldName` as `$fieldNameAs`";

            if($tIdx !== count($tbsToRelate) - 1){
                $tbSelectionStr .= ",";
            }
            $tbSelectionStr .= " ";
        }

        $tbForeignId = $tbForeign[$tableRelate];
        $tbJoinStr .= "INNER JOIN `$tableRelate` ON `$current_table`.`$tbForeignId` = `$tableRelate`.`$tbIdToRelate`";
    }

    $queryStr .= $tbSelectionStr;
    $queryStr .= "FROM `$current_table` ";
    $queryStr .= $tbJoinStr;
    $queryStr .= "ORDER BY `$current_table`.id";

}else{
    $queryStr = "SELECT * FROM `$current_table`";
}

$datas = query($queryStr);
$total_datas = count($datas);

$limit = 10;
$current_page = (int) ($_GET['page'] ?? 1);
$start = ($current_page - 1) * $limit;
$total_pages = (int) ceil(count($datas) / $limit);



if(isset($_GET['search']) || isset($_POST['search-btn'])){
    $keyword = $_GET['search'] ?? $_POST['search'];
    $datas = search($keyword, $current_table);
    $total_datas = count($datas);
    $total_pages = (int) ceil(count($datas) / $limit);
    $datas = searchWithLimit($keyword, $start, $limit, $current_table);
}else{
    $datas = query("$queryStr LIMIT $start, $limit");
}




?>

<?php require_once "../partials/header.php" ?>

<div class="d-flex">
    <?php require_once "../partials/sidebar.php" ?>
    <div class="container-fluid py-5 mx-5" style="overflow-y: auto; max-height: 100vh;">
        <h1>Data Rekam Medis</h1>
        
        <p class="text-muted">Table managemen rekam medis EHealth</p>
        <div class="custom-underline w-100"></div>

        <div class="d-flex align-items-center justify-content-between mb-3" style="margin-top: 4rem;">
            <!-- search -->
            <form action="" method="post" style="width: 100%; max-width: 480px;">
                <div class="input-group">
                    <input type="text" name="search" id="search-input" class="form-control" data-table="<?= $current_table ?>" value="<?= $keyword ?? ''?>">
                    <button type="submit" name="search-btn" class="btn btn-primary"><i class="fa-solid fa-magnifying-glass"></i></button>
                </div>
            </form>
            <a href="./tambah.php" class="btn btn-outline-primary">Tambah</a>
        </div>

        <?php if(isset($_SESSION['process-success'])): ?>
            <div class="alert alert-success mt-4" role="alert" style="max-width: 480px;">
                <?= $_SESSION['process-success']?>

                <?php unset($_SESSION['process-success']); ?>
            </div>
        <?php endif; ?>


        <?php if(isset($_SESSION['process-failed'])): ?>
            <div class="alert alert-danger mt-4" role="alert" style="max-width: 480px;">
                <?= $_SESSION['process-failed']?>

                <?php unset($_SESSION['process-failed']); ?>
            </div>
        <?php endif; ?>


        <div class="table-responsive" id="table-element">
            <?php if(count($datas) > 0): ?>
                <form action="" method="post" id="checked_form">
                    <table class="table table-striped table-bordered caption-top">
                        <caption>Saat ini <?= $total_datas ?> rekam medis.</caption>
                        <tr>
                            <th>
                                <input type="checkbox" class="form-check-input mx-auto d-block" id="select-all-checks" style="width: 20px; height: 20px;">
                            </th>
                            <th>No</th>
                            <th>Nama Pasien (id)</th>
                            <th>Keluhan</th>
                            <th>Nama Dokter (id)</th>
                            <th>Diagnosa</th>
                            <th>Nama Poliklinik (id)</th>
                            <th>Tanggal Periksa</th>
                            <th>Obat</th>
                        </tr>

                        <?php $i = $start + 1; foreach($datas as $data): ?>
                            <tr>
                                <td>
                                    <input type="checkbox" class="form-check-input checks mx-auto d-block" style="width: 20px; height: 20px;" name="<?= "select_single_$i"?>" value="<?= $data['id']?>">
                                </td>
                                <td><?= $i?></td>
                                <td><?=$data['nama_pasien'] ?> (<?= $data['id_pasien']?>)</td>
                                <td><?= $data['keluhan']?></td>
                                <td><?=$data['nama_dokter'] ?> (<?= $data['id_dokter']?>)</td>
                                <td><?= $data['diagnosa']?></td>
                                <td><?=$data['nama_poliklinik'] ?> (<?= $data['id_poliklinik']?>)</td>
                                <td><?= $data['tgl_periksa']?></td>

                                <?php 
                                $id_rm = $data['id'];

                                $rm_obat = query("SELECT * FROM `tb_rekammedis_obat` WHERE `id_rekammedis` = $id_rm");

                                $rm_obat_id = array_map(fn($e) => $e['id_obat'], $rm_obat);
                                $obat = [];

                                foreach($rm_obat_id as $rmo){
                                    $obat[] = query("SELECT * FROM `tb_obat` WHERE `id` = $rmo")[0];
                                }

                                $strObat = implode(', ', array_map(fn($o) => $o['nama_obat'], $obat));
                                ?>
                                <td style="max-width: 16rem;">
                                    <?= $strObat?>
                                </td>
                            </tr>
                        <?php $i++; endforeach; ?>
                    </table>
                </form>
                <nav aria-label="Page navigation example">
                    <ul class="pagination gap-0">
                        <li class="page-item <?= $current_page == 1 ? 'disabled' : ''?>">
                            <a class="page-link" href="?<?= isset($keyword) ? "search=$keyword&" : ''?>page=<?= $current_page - 1?>" aria-label="Previous">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>

                        <?php for($j = 0; $j < $total_pages; $j++): ?>
                                <li class="page-item <?= $j + 1 == $current_page ? 'active' : '' ?>"><a class="page-link" href="?<?= isset($keyword) ? "search=$keyword&" : ''?>page=<?= $j + 1 ?>"><?= $j + 1?></a></li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?= $current_page == $total_pages ? 'disabled' : ''?>">
                            <a class="page-link" href="?<?= isset($keyword) ? "search=$keyword&" : ''?>page=<?= $current_page + 1?>" aria-label="Next">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php else: ?>
                <h4 class="mt-3 mb-4">Saat ini tidak ada data.</h4>
            <?php endif; ?>
        </div>


        <div class="d-flex gap-2">
            <button class="btn btn-warning edit">Edit</button>
            <button class="btn btn-danger hapus">Hapus</button>
        </div>

    </div>
</div>

<script src="<?= "$base_url/assets/js/checks.js"?>"></script>
<script src="<?= "$base_url/assets/js/ajax.js"?>"></script>
<?php require_once "../partials/footer.php" ?>