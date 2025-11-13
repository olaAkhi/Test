<?php
// Expected variables from UserController::viewTransactionHistory():
// $pageTitle, $transactions, $totalPages, $currentPage, $totalTransactions
// $currentUser is also available globally if user is logged in.
?>
<section>
    <h1><?php echo isset($pageTitle) ? htmlspecialchars($pageTitle) : 'Transaction History'; ?></h1>

    <?php if (isset($currentUser)): ?>
        <p class="lead">Review your account transactions below. Current Balance: <strong>$<?php echo htmlspecialchars(number_format($currentUser['balance'], 2)); ?></strong></p>

        <?php if (!empty($transactions)): ?>
            <div class="table-responsive">
                <table class="table table-striped table-hover">
                    <thead class="table-dark">
                        <tr>
                            <th>Date</th>
                            <th>Type</th>
                            <th>Description</th>
                            <th class="text-end">Amount</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($transactions as $transaction): ?>
                            <tr>
                                <td><?php echo htmlspecialchars(date("M d, Y H:i", strtotime($transaction['transaction_date']))); ?></td>
                                <td><?php echo htmlspecialchars(ucfirst($transaction['type'])); ?></td>
                                <td>
                                    <?php
                                    echo htmlspecialchars($transaction['description']);
                                    if ($transaction['type'] === 'purchase' || $transaction['type'] === 'refund') {
                                        if (!empty($transaction['service_name'])) {
                                            echo " (Service: " . htmlspecialchars($transaction['service_name']) . ")";
                                        } elseif ($transaction['related_service_id']) {
                                            echo " (Order ID: #" . htmlspecialchars($transaction['related_service_id']) . ")";
                                        }
                                    }
                                    ?>
                                </td>
                                <td class="text-end">
                                    <?php
                                    $amount = (float)$transaction['amount'];
                                    $class = '';
                                    $prefix = '';
                                    if ($transaction['type'] === 'purchase') {
                                        $class = 'text-danger';
                                        $prefix = '-$';
                                    } elseif ($transaction['type'] === 'refund' || $transaction['type'] === 'deposit') {
                                        $class = 'text-success';
                                        $prefix = '+$';
                                    }
                                    echo "<span class='{$class}'>" . $prefix . htmlspecialchars(number_format(abs($amount), 2)) . "</span>";
                                    ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <?php if (isset($totalPages) && $totalPages > 1): ?>
                <nav aria-label="Transaction Pagination">
                    <ul class="pagination justify-content-center mt-4">
                        <?php if ($currentPage > 1): ?>
                            <li class="page-item"><a class="page-link" href="index.php?action=transaction_history&page=<?php echo $currentPage - 1; ?>">Previous</a></li>
                        <?php else: ?>
                            <li class="page-item disabled"><span class="page-link">Previous</span></li>
                        <?php endif; ?>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo ($i == $currentPage) ? 'active' : ''; ?>">
                                <a class="page-link" href="index.php?action=transaction_history&page=<?php echo $i; ?>"><?php echo $i; ?></a>
                            </li>
                        <?php endfor; ?>

                        <?php if ($currentPage < $totalPages): ?>
                            <li class="page-item"><a class="page-link" href="index.php?action=transaction_history&page=<?php echo $currentPage + 1; ?>">Next</a></li>
                        <?php else: ?>
                            <li class="page-item disabled"><span class="page-link">Next</span></li>
                        <?php endif; ?>
                    </ul>
                </nav>
            <?php endif; ?>
             <p class="text-center text-muted mt-3">Showing <?php echo count($transactions); ?> of <?php echo htmlspecialchars($totalTransactions ?? 0); ?> transactions.</p>

        <?php else: ?>
            <div class="alert alert-info mt-3" role="alert">
                You have no transactions yet.
            </div>
        <?php endif; ?>
         <div class="mt-4">
            <a href="index.php?action=dashboard" class="btn btn-secondary">Back to Dashboard</a>
        </div>
    <?php else: ?>
        <p class="alert alert-warning">Please <a href="index.php?action=login">login</a> to view your transaction history.</p>
    <?php endif; ?>
</section>
