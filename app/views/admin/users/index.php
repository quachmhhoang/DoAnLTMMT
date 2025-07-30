<?php
require_once __DIR__ . '/../../../helpers/SessionHelper.php';
$title = 'Quản lý người dùng - Admin';
ob_start();
?>

<div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
    <h1 class="h2">Quản lý người dùng</h1>
</div>

<?php if (!empty($users)): ?>
<div class="card">
    <div class="card-body">
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Username</th>
                        <th>Họ tên</th>
                        <th>Email</th>
                        <th>Điện thoại</th>
                        <th>Vai trò</th>
                        <th>Thao tác</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                        <tr>
                            <td><?php echo $user->user_id; ?></td>
                            <td><?php echo htmlspecialchars($user->username); ?></td>
                            <td><?php echo htmlspecialchars($user->full_name); ?></td>
                            <td><?php echo htmlspecialchars($user->email); ?></td>
                            <td><?php echo htmlspecialchars($user->phone); ?></td>
                            <td>
                                <?php if ($user->role === 'admin'): ?>
                                    <span class="badge bg-danger">Admin</span>
                                <?php else: ?>
                                    <span class="badge bg-primary">Customer</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <?php if ($user->role !== 'admin'): ?>
                                    <a href="/admin/users/delete/<?php echo $user->user_id; ?>" 
                                       class="btn btn-sm btn-outline-danger"
                                       onclick="return confirm('Bạn có chắc muốn xóa người dùng này?')">
                                        <i class="fas fa-trash"></i> Xóa
                                    </a>
                                <?php endif; ?>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
<?php else: ?>
<div class="text-center">
    <i class="fas fa-users fa-3x text-muted mb-3"></i>
    <h4>Chưa có người dùng nào</h4>
</div>
<?php endif; ?>

<?php
$content = ob_get_clean();
include __DIR__ . '/../../layout/admin.php';
?>
