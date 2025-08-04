<?php
if (!SessionHelper::isAdmin()) {
    header('Location: /login');
    exit();
}

$pageTitle = 'Quản lý khuyến mãi';
include __DIR__ . '/../../layout/admin_header.php';
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h3 class="card-title">Danh sách khuyến mãi</h3>
                    <a href="/admin/promotions/add" class="btn btn-primary">
                        <i class="fas fa-plus"></i> Thêm khuyến mãi
                    </a>
                </div>
                <div class="card-body">
                    <?php if (SessionHelper::getFlash('success')): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <?= SessionHelper::getFlash('success') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <?php if (SessionHelper::getFlash('error')): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <?= SessionHelper::getFlash('error') ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead class="table-dark">
                                <tr>
                                    <th>ID</th>
                                    <th>Tên khuyến mãi</th>
                                    <th>Mô tả</th>
                                    <th>Giảm giá (%)</th>
                                    <th>Ngày bắt đầu</th>
                                    <th>Ngày kết thúc</th>
                                    <th>Trạng thái</th>
                                    <th>Thao tác</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if (!empty($promotions)): ?>
                                    <?php foreach ($promotions as $promotion): ?>
                                        <?php
                                        $today = date('Y-m-d');
                                        $status = '';
                                        $statusClass = '';
                                        
                                        if ($promotion->start_date > $today) {
                                            $status = 'Sắp diễn ra';
                                            $statusClass = 'badge bg-warning';
                                        } elseif ($promotion->end_date < $today) {
                                            $status = 'Đã kết thúc';
                                            $statusClass = 'badge bg-secondary';
                                        } else {
                                            $status = 'Đang diễn ra';
                                            $statusClass = 'badge bg-success';
                                        }
                                        ?>
                                        <tr>
                                            <td><?= $promotion->promotion_id ?></td>
                                            <td>
                                                <strong><?= htmlspecialchars($promotion->promotion_name ?? '') ?></strong>
                                            </td>
                                            <td>
                                                <div class="text-truncate" style="max-width: 200px;" title="<?= htmlspecialchars($promotion->description ?? '') ?>">
                                                    <?= htmlspecialchars($promotion->description ?? '') ?>
                                                </div>
                                            </td>
                                            <td>
                                                <span class="badge bg-info"><?= $promotion->discount_percent ?>%</span>
                                            </td>
                                            <td><?= date('d/m/Y', strtotime($promotion->start_date)) ?></td>
                                            <td><?= date('d/m/Y', strtotime($promotion->end_date)) ?></td>
                                            <td>
                                                <span class="<?= $statusClass ?>"><?= $status ?></span>
                                            </td>
                                            <td>
                                                <div class="btn-group" role="group">
                                                    <a href="/admin/promotions/delete/<?= $promotion->promotion_id ?>" 
                                                       class="btn btn-sm btn-danger"
                                                       onclick="return confirm('Bạn có chắc chắn muốn xóa khuyến mãi này?')">
                                                        <i class="fas fa-trash"></i>
                                                    </a>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="8" class="text-center">
                                            <div class="py-4">
                                                <i class="fas fa-tags fa-3x text-muted mb-3"></i>
                                                <p class="text-muted">Chưa có khuyến mãi nào</p>
                                                <a href="/admin/promotions/add" class="btn btn-primary">
                                                    <i class="fas fa-plus"></i> Thêm khuyến mãi đầu tiên
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
.table th {
    border-top: none;
    font-weight: 600;
}

.badge {
    font-size: 0.75em;
}

.btn-group .btn {
    margin-right: 2px;
}

.text-truncate {
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}
</style>

<?php include __DIR__ . '/../../layout/admin_footer.php'; ?>
