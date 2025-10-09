<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pharmacy - Inventory Management - HMS</title>
    <link rel="stylesheet" href="/assets/css/common.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
</head>
<body class="pharmacy-theme">
    <!-- Header -->
    <?php include APPPATH . 'Views/template/header.php'; ?>

    <div class="main-container">
        <!-- Sidebar -->
        <?php include APPPATH . 'Views/pharmacists/components/sidebar.php'; ?>

        <!-- Main Content -->
        <main class="content">
            <h1 class="page-title">Inventory Management</h1>

            <!-- Quick Actions -->
            <div class="quick-actions">
                <a href="#items" class="btn btn-primary"><i class="fas fa-list"></i> View Items</a>
                <a href="#receive" class="btn btn-secondary"><i class="fas fa-truck"></i> Receive Stock</a>
                <a href="#adjust" class="btn btn-secondary"><i class="fas fa-sliders-h"></i> Adjust Inventory</a>
                <a href="#low-stock" class="btn btn-warning"><i class="fas fa-arrow-down"></i> Low Stock</a>
                <a href="#expired" class="btn btn-danger"><i class="fas fa-skull-crossbones"></i> Expired</a>
            </div>

            <!-- Filters -->
            <div class="table-container" style="margin-top: 1.5rem">
                <h3 style="margin-bottom: 1rem">Filters</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem">
                    <div>
                        <label style="display:block; font-weight:600; margin-bottom: .5rem">Search</label>
                        <input type="text" id="search" placeholder="Item code or name" style="width:100%; padding:.6rem; border:1px solid #e2e8f0; border-radius:8px" />
                    </div>
                    <div>
                        <label style="display:block; font-weight:600; margin-bottom: .5rem">Category</label>
                        <select id="category" style="width:100%; padding:.6rem; border:1px solid #e2e8f0; border-radius:8px">
                            <option value="">All</option>
                            <option>Antibiotics</option>
                            <option>Cardiology</option>
                            <option>Endocrine</option>
                            <option>OTC</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-weight:600; margin-bottom: .5rem">Status</label>
                        <select id="status" style="width:100%; padding:.6rem; border:1px solid #e2e8f0; border-radius:8px">
                            <option value="">All</option>
                            <option value="ok">OK</option>
                            <option value="low">Low Stock</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Inventory Items -->
            <div class="table-container" style="margin-top: 1.5rem">
                <h3 id="items" style="margin-bottom: 1rem">Items</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Item Code</th>
                            <th>Name</th>
                            <th>Category</th>
                            <th>Stock</th>
                            <th>Unit</th>
                            <th>Min Level</th>
                            <th>Expiry</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($inventory_items)): ?>
                            <?php foreach ($inventory_items as $item): ?>
                                <tr>
                                    <td><?= $item['item_code'] ?></td>
                                    <td><?= $item['name'] ?></td>
                                    <td><?= $item['category'] ?></td>
                                    <td><?= $item['stock_quantity'] ?></td>
                                    <td><?= $item['unit'] ?></td>
                                    <td><?= $item['min_stock_level'] ?></td>
                                    <td><?= $item['expiry_date'] ?></td>
                                    <td>
                                        <a href="#receive" class="btn btn-secondary" style="padding:.3rem .8rem; font-size:.8rem">Receive</a>
                                        <a href="#adjust" class="btn btn-primary" style="padding:.3rem .8rem; font-size:.8rem">Adjust</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" style="text-align: center;">No inventory items found</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Low Stock -->
            <div class="table-container" style="margin-top: 2rem">
                <h3 id="low-stock" style="margin-bottom: 1rem">Low Stock</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Item Code</th>
                            <th>Name</th>
                            <th>Stock</th>
                            <th>Min Level</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($low_stock_items)): ?>
                            <?php foreach ($low_stock_items as $item): ?>
                                <tr>
                                    <td><?= $item['item_code'] ?></td>
                                    <td><?= $item['name'] ?></td>
                                    <td><?= $item['stock_quantity'] ?></td>
                                    <td><?= $item['min_stock_level'] ?></td>
                                    <td>
                                        <a href="#receive" class="btn btn-warning" style="padding:.3rem .8rem; font-size:.8rem">Receive</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No low stock items</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Expired -->
            <div class="table-container" style="margin-top: 2rem">
                <h3 id="expired" style="margin-bottom: 1rem">Expired</h3>
                <table class="table">
                    <thead>
                        <tr>
                            <th>Item Code</th>
                            <th>Name</th>
                            <th>Expiry</th>
                            <th>Qty</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($expired_items)): ?>
                            <?php foreach ($expired_items as $item): ?>
                                <tr>
                                    <td><?= $item['item_code'] ?></td>
                                    <td><?= $item['name'] ?></td>
                                    <td><?= $item['expiry_date'] ?></td>
                                    <td><?= $item['stock_quantity'] ?></td>
                                    <td>
                                        <a href="#" class="btn btn-danger" style="padding:.3rem .8rem; font-size:.8rem" onclick="removeExpiredItem('<?= $item['item_code'] ?>')">Remove</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" style="text-align: center;">No expired items</td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>

            <!-- Receive Stock -->
            <div class="table-container" style="margin-top: 2rem">
                <h3 id="receive" style="margin-bottom: 1rem">Receive Stock</h3>
                <form id="receive-form">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem">
                        <div>
                            <label style="display:block; font-weight:600; margin-bottom: .5rem">Item Code</label>
                            <input id="rCode" name="rCode" type="text" placeholder="MED-..." style="width:100%; padding:.75rem; border:1px solid #e2e8f0; border-radius:8px" required />
                        </div>
                        <div>
                            <label style="display:block; font-weight:600; margin-bottom: .5rem">Quantity</label>
                            <input id="rQty" name="rQty" type="number" min="1" placeholder="e.g., 100" style="width:100%; padding:.75rem; border:1px solid #e2e8f0; border-radius:8px" required />
                        </div>
                        <div>
                            <label style="display:block; font-weight:600; margin-bottom: .5rem">Lot/Batch</label>
                            <input id="rLot" name="rLot" type="text" placeholder="e.g., LOT-2025-09A" style="width:100%; padding:.75rem; border:1px solid #e2e8f0; border-radius:8px" />
                        </div>
                        <div>
                            <label style="display:block; font-weight:600; margin-bottom: .5rem">Expiry</label>
                            <input id="rExp" name="rExp" type="date" style="width:100%; padding:.75rem; border:1px solid #e2e8f0; border-radius:8px" />
                        </div>
                    </div>
                    <div class="quick-actions" style="justify-content: flex-end; margin-top: 1rem">
                        <button type="submit" class="btn btn-secondary"><i class="fas fa-truck"></i> Receive</button>
                    </div>
                </form>
            </div>

            <!-- Adjust Inventory -->
            <div class="table-container" style="margin-top: 2rem">
                <h3 id="adjust" style="margin-bottom: 1rem">Adjust Inventory</h3>
                <form id="adjust-form">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem">
                        <div>
                            <label style="display:block; font-weight:600; margin-bottom: .5rem">Item Code</label>
                            <input id="aCode" name="aCode" type="text" placeholder="MED-..." style="width:100%; padding:.75rem; border:1px solid #e2e8f0; border-radius:8px" required />
                        </div>
                        <div>
                            <label style="display:block; font-weight:600; margin-bottom: .5rem">Change (±)</label>
                            <input id="aDelta" name="aDelta" type="number" placeholder="e.g., -5 or 10" style="width:100%; padding:.75rem; border:1px solid #e2e8f0; border-radius:8px" required />
                        </div>
                        <div>
                            <label style="display:block; font-weight:600; margin-bottom: .5rem">Reason</label>
                            <select id="aReason" name="aReason" style="width:100%; padding:.75rem; border:1px solid #e2e8f0; border-radius:8px">
                                <option>Correction</option>
                                <option>Damage</option>
                                <option>Loss</option>
                                <option>Donation</option>
                            </select>
                        </div>
                    </div>
                    <div class="quick-actions" style="justify-content: flex-end; margin-top: 1rem">
                        <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Apply Adjustment</button>
                    </div>
                </form>
            </div>

        </main>
    </div>

    <script>
        function handleLogout(){ if(confirm('Are you sure you want to logout?')) window.location.href = '<?= base_url('logout') ?>'; }

        // Receive Stock Form
        document.getElementById('receive-form').addEventListener('submit', function(e){
            e.preventDefault();

            const formData = new FormData(this);
            const data = Object.fromEntries(formData);

            fetch('<?= base_url('pharmacists/receiveStock') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert(result.message);
                    this.reset();
                    location.reload(); // Refresh to show updated data
                } else {
                    alert('Error: ' + (result.errors ? Object.values(result.errors).join(', ') : result.message));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });

        // Adjust Inventory Form
        document.getElementById('adjust-form').addEventListener('submit', function(e){
            e.preventDefault();

            const formData = new FormData(this);
            const data = Object.fromEntries(formData);

            fetch('<?= base_url('pharmacists/adjustInventory') ?>', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: new URLSearchParams(data)
            })
            .then(response => response.json())
            .then(result => {
                if (result.success) {
                    alert(result.message);
                    this.reset();
                    location.reload(); // Refresh to show updated data
                } else {
                    alert('Error: ' + (result.errors ? Object.values(result.errors).join(', ') : result.message));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('An error occurred. Please try again.');
            });
        });

        // Remove expired item
        function removeExpiredItem(itemCode) {
            if (confirm('Are you sure you want to remove this expired item?')) {
                fetch('<?= base_url('pharmacists/removeExpiredItem') ?>', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                        'X-Requested-With': 'XMLHttpRequest'
                    },
                    body: new URLSearchParams({ item_code: itemCode })
                })
                .then(response => response.json())
                .then(result => {
                    if (result.success) {
                        alert(result.message);
                        location.reload();
                    } else {
                        alert('Error: ' + result.message);
                    }
                })
                .catch(error => {
                    console.error('Error:', error);
                    alert('An error occurred. Please try again.');
                });
            }
        }

        // Filter functionality
        document.getElementById('search').addEventListener('input', filterTable);
        document.getElementById('category').addEventListener('change', filterTable);
        document.getElementById('status').addEventListener('change', filterTable);

        function filterTable() {
            const search = document.getElementById('search').value.toLowerCase();
            const category = document.getElementById('category').value;
            const status = document.getElementById('status').value;

            const params = new URLSearchParams();
            if (search) params.append('search', search);
            if (category) params.append('category', category);
            if (status) params.append('status', status);

            window.location.href = '<?= base_url('pharmacists/inventory') ?>?' + params.toString();
        }
    </script>
</body>
</html>
