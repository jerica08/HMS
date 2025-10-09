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

            <!-- Error Notice -->
            <?php if (isset($error)): ?>
                <div style="background: #fee2e2; border: 1px solid #fecaca; color: #dc2626; padding: 1rem; border-radius: 8px; margin-bottom: 2rem; text-align: center;">
                    <i class="fas fa-exclamation-triangle" style="margin-right: 0.5rem;"></i>
                    <?php echo $error; ?>
                </div>
            <?php endif; ?>

            <!-- Quick Actions -->
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem; margin-bottom: 2rem;">
                <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; margin-bottom: 1rem;">
                        <i class="fas fa-boxes"></i>
                    </div>
                    <h3 style="margin: 0 0 0.5rem 0; color: #667eea; font-size: 2rem; font-weight: bold;"><?php echo count($inventory_items ?? []); ?></h3>
                    <p style="margin: 0; color: #6b7280; font-size: 0.9rem;">Total Items</p>
                </div>

                <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; margin-bottom: 1rem;">
                        <i class="fas fa-arrow-down"></i>
                    </div>
                    <h3 style="margin: 0 0 0.5rem 0; color: #f093fb; font-size: 2rem; font-weight: bold;"><?php echo count($low_stock_items ?? []); ?></h3>
                    <p style="margin: 0; color: #6b7280; font-size: 0.9rem;">Low Stock Items</p>
                </div>

                <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); text-align: center;">
                    <div style="width: 60px; height: 60px; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); border-radius: 12px; display: inline-flex; align-items: center; justify-content: center; color: white; font-size: 1.5rem; margin-bottom: 1rem;">
                        <i class="fas fa-skull-crossbones"></i>
                    </div>
                    <h3 style="margin: 0 0 0.5rem 0; color: #43e97b; font-size: 2rem; font-weight: bold;"><?php echo count($expired_items ?? []); ?></h3>
                    <p style="margin: 0; color: #6b7280; font-size: 0.9rem;">Expired Items</p>
                </div>
            </div>

            <!-- Quick Actions -->
            <div style="display: flex; gap: 1rem; margin-bottom: 2rem; flex-wrap: wrap;">
                <button onclick="showSection('items')" class="btn btn-primary" style="background: #667eea; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; color: white; cursor: pointer;">
                    <i class="fas fa-list"></i> View Items
                </button>
                <button onclick="showSection('receive')" class="btn btn-secondary" style="background: #6b7280; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; color: white; cursor: pointer;">
                    <i class="fas fa-truck"></i> Receive Stock
                </button>
                <button onclick="showSection('adjust')" class="btn btn-secondary" style="background: #f59e0b; border: none; padding: 0.75rem 1.5rem; border-radius: 8px; color: white; cursor: pointer;">
                    <i class="fas fa-sliders-h"></i> Adjust Inventory
                </button>
            </div>

            <!-- Filters -->
            <div style="background: white; padding: 1.5rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-bottom: 2rem;">
                <h3 style="margin: 0 0 1.5rem 0; color: #1f2937;">Filters</h3>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 1rem;">
                    <div>
                        <label style="display:block; font-weight:600; margin-bottom: .5rem; color: #374151;">Search</label>
                        <input type="text" id="search" placeholder="Item code or name" style="width:100%; padding:.6rem; border:1px solid #d1d5db; border-radius:6px; font-size: 0.9rem;" />
                    </div>
                    <div>
                        <label style="display:block; font-weight:600; margin-bottom: .5rem; color: #374151;">Category</label>
                        <select id="category" style="width:100%; padding:.6rem; border:1px solid #d1d5db; border-radius:6px; font-size: 0.9rem;">
                            <option value="">All Categories</option>
                            <option>Antibiotics</option>
                            <option>Cardiology</option>
                            <option>Endocrine</option>
                            <option>OTC</option>
                        </select>
                    </div>
                    <div>
                        <label style="display:block; font-weight:600; margin-bottom: .5rem; color: #374151;">Status</label>
                        <select id="status" style="width:100%; padding:.6rem; border:1px solid #d1d5db; border-radius:6px; font-size: 0.9rem;">
                            <option value="">All Status</option>
                            <option value="ok">In Stock</option>
                            <option value="low">Low Stock</option>
                            <option value="expired">Expired</option>
                        </select>
                    </div>
                </div>
            </div>

            <!-- Inventory Items -->
            <div style="background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden;">
                <div style="padding: 1.5rem; border-bottom: 1px solid #e5e7eb; background: #f8fafc;">
                    <h3 id="items" style="margin: 0; color: #1f2937;">Inventory Items</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; min-width: 800px;">
                        <thead>
                            <tr style="background: #f8fafc;">
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Item Code</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Name</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Category</th>
                                <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Stock</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Unit</th>
                                <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Min Level</th>
                                <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Expiry</th>
                                <th style="padding: 1rem; text-align: center; font-weight: 600; color: #374151; border-bottom: 1px solid #e5e7eb;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($inventory_items)): ?>
                                <?php foreach ($inventory_items as $item): ?>
                                    <tr style="border-bottom: 1px solid #f3f4f6; <?php echo ($item['stock_quantity'] <= ($item['min_stock_level'] ?? 0)) ? 'background: #fef3c7;' : ''; ?>">
                                        <td style="padding: 1rem; font-weight: 500; color: #1f2937;"><?php echo esc($item['item_code']); ?></td>
                                        <td style="padding: 1rem; color: #374151;"><?php echo esc($item['name']); ?></td>
                                        <td style="padding: 1rem; color: #374151;">
                                            <span style="background: #e0f2fe; color: #0284c7; padding: 0.25rem 0.75rem; border-radius: 9999px; font-size: 0.75rem; font-weight: 500;">
                                                <?php echo esc($item['category']); ?>
                                            </span>
                                        </td>
                                        <td style="padding: 1rem; text-align: center; font-weight: 600; <?php echo ($item['stock_quantity'] <= ($item['min_stock_level'] ?? 0)) ? 'color: #dc2626;' : 'color: #059669;'; ?>">
                                            <?php echo esc($item['stock_quantity']); ?>
                                        </td>
                                        <td style="padding: 1rem; text-align: center; color: #374151;"><?php echo esc($item['unit']); ?></td>
                                        <td style="padding: 1rem; text-align: center; color: #6b7280;"><?php echo esc($item['min_stock_level']); ?></td>
                                        <td style="padding: 1rem; text-align: center; color: #374151;">
                                            <?php
                                            $expiry_date = strtotime($item['expiry_date']);
                                            $today = strtotime(date('Y-m-d'));
                                            if ($expiry_date < $today) {
                                                echo '<span style="background: #fee2e2; color: #dc2626; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem;">Expired</span>';
                                            } elseif ($expiry_date <= strtotime('+30 days')) {
                                                echo '<span style="background: #fef3c7; color: #d97706; padding: 0.25rem 0.5rem; border-radius: 4px; font-size: 0.75rem;">Expiring Soon</span>';
                                            } else {
                                                echo date('M j, Y', $expiry_date);
                                            }
                                            ?>
                                        </td>
                                        <td style="padding: 1rem; text-align: center;">
                                            <button onclick="receiveStock('<?php echo esc($item['item_code']); ?>')" class="btn btn-secondary" style="background: #6b7280; border: none; padding: 0.5rem 1rem; border-radius: 6px; color: white; cursor: pointer; font-size: 0.8rem; margin-right: 0.5rem;">
                                                <i class="fas fa-truck"></i> Receive
                                            </button>
                                            <button onclick="adjustInventory('<?php echo esc($item['item_code']); ?>')" class="btn btn-primary" style="background: #667eea; border: none; padding: 0.5rem 1rem; border-radius: 6px; color: white; cursor: pointer; font-size: 0.8rem;">
                                                <i class="fas fa-sliders-h"></i> Adjust
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="8" style="text-align: center; padding: 3rem; color: #6b7280; font-style: italic;">
                                        <i class="fas fa-box-open" style="font-size: 3rem; margin-bottom: 1rem; display: block; opacity: 0.5;"></i>
                                        No inventory items found
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Low Stock Items -->
            <div style="background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden; margin-top: 2rem;">
                <div style="padding: 1.5rem; border-bottom: 1px solid #e5e7eb; background: #fef3c7;">
                    <h3 id="low-stock" style="margin: 0; color: #92400e;">Low Stock Items</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; min-width: 600px;">
                        <thead>
                            <tr style="background: #fef3c7;">
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #92400e; border-bottom: 1px solid #f59e0b;">Item Code</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #92400e; border-bottom: 1px solid #f59e0b;">Name</th>
                                <th style="padding: 1rem; text-align: center; font-weight: 600; color: #92400e; border-bottom: 1px solid #f59e0b;">Stock</th>
                                <th style="padding: 1rem; text-align: center; font-weight: 600; color: #92400e; border-bottom: 1px solid #f59e0b;">Min Level</th>
                                <th style="padding: 1rem; text-align: center; font-weight: 600; color: #92400e; border-bottom: 1px solid #f59e0b;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($low_stock_items)): ?>
                                <?php foreach ($low_stock_items as $item): ?>
                                    <tr style="border-bottom: 1px solid #fed7aa; background: #fffbeb;">
                                        <td style="padding: 1rem; font-weight: 500; color: #92400e;"><?php echo esc($item['item_code']); ?></td>
                                        <td style="padding: 1rem; color: #92400e;"><?php echo esc($item['name']); ?></td>
                                        <td style="padding: 1rem; text-align: center; font-weight: 600; color: #dc2626;"><?php echo esc($item['stock_quantity']); ?></td>
                                        <td style="padding: 1rem; text-align: center; color: #6b7280;"><?php echo esc($item['min_stock_level']); ?></td>
                                        <td style="padding: 1rem; text-align: center;">
                                            <button onclick="receiveStock('<?php echo esc($item['item_code']); ?>')" class="btn btn-warning" style="background: #f59e0b; border: none; padding: 0.5rem 1rem; border-radius: 6px; color: white; cursor: pointer; font-size: 0.8rem;">
                                                <i class="fas fa-truck"></i> Receive Stock
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 3rem; color: #6b7280; font-style: italic;">
                                        <i class="fas fa-check-circle" style="font-size: 3rem; margin-bottom: 1rem; display: block; opacity: 0.5; color: #10b981;"></i>
                                        All items are sufficiently stocked
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Expired Items -->
            <div style="background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); overflow: hidden; margin-top: 2rem;">
                <div style="padding: 1.5rem; border-bottom: 1px solid #e5e7eb; background: #fee2e2;">
                    <h3 id="expired" style="margin: 0; color: #dc2626;">Expired Items</h3>
                </div>
                <div style="overflow-x: auto;">
                    <table style="width: 100%; border-collapse: collapse; min-width: 600px;">
                        <thead>
                            <tr style="background: #fee2e2;">
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #dc2626; border-bottom: 1px solid #fca5a5;">Item Code</th>
                                <th style="padding: 1rem; text-align: left; font-weight: 600; color: #dc2626; border-bottom: 1px solid #fca5a5;">Name</th>
                                <th style="padding: 1rem; text-align: center; font-weight: 600; color: #dc2626; border-bottom: 1px solid #fca5a5;">Expiry Date</th>
                                <th style="padding: 1rem; text-align: center; font-weight: 600; color: #dc2626; border-bottom: 1px solid #fca5a5;">Quantity</th>
                                <th style="padding: 1rem; text-align: center; font-weight: 600; color: #dc2626; border-bottom: 1px solid #fca5a5;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (!empty($expired_items)): ?>
                                <?php foreach ($expired_items as $item): ?>
                                    <tr style="border-bottom: 1px solid #fecaca; background: #fef2f2;">
                                        <td style="padding: 1rem; font-weight: 500; color: #dc2626;"><?php echo esc($item['item_code']); ?></td>
                                        <td style="padding: 1rem; color: #dc2626;"><?php echo esc($item['name']); ?></td>
                                        <td style="padding: 1rem; text-align: center; color: #dc2626; font-weight: 600;"><?php echo date('M j, Y', strtotime($item['expiry_date'])); ?></td>
                                        <td style="padding: 1rem; text-align: center; color: #dc2626; font-weight: 600;"><?php echo esc($item['stock_quantity']); ?></td>
                                        <td style="padding: 1rem; text-align: center;">
                                            <button onclick="removeExpiredItem('<?php echo esc($item['item_code']); ?>')" class="btn btn-danger" style="background: #dc2626; border: none; padding: 0.5rem 1rem; border-radius: 6px; color: white; cursor: pointer; font-size: 0.8rem;">
                                                <i class="fas fa-trash"></i> Remove
                                            </button>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 3rem; color: #6b7280; font-style: italic;">
                                        <i class="fas fa-check-circle" style="font-size: 3rem; margin-bottom: 1rem; display: block; opacity: 0.5; color: #10b981;"></i>
                                        No expired items found
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Receive Stock Form -->
            <div style="background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-top: 2rem;">
                <div style="padding: 1.5rem; border-bottom: 1px solid #e5e7eb; background: #f0f9ff;">
                    <h3 id="receive" style="margin: 0; color: #0369a1;">
                        <i class="fas fa-truck" style="margin-right: 0.5rem;"></i>Receive Stock
                    </h3>
                </div>
                <form id="receive-form" style="padding: 1.5rem;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                        <div>
                            <label style="display:block; font-weight:600; margin-bottom: .5rem; color: #374151;">Item Code *</label>
                            <input id="rCode" name="item_code" type="text" placeholder="e.g., MED-001" style="width:100%; padding:.75rem; border:1px solid #d1d5db; border-radius:6px; font-size: 0.9rem;" required />
                        </div>
                        <div>
                            <label style="display:block; font-weight:600; margin-bottom: .5rem; color: #374151;">Name *</label>
                            <input id="rName" name="name" type="text" placeholder="e.g., Paracetamol 500mg" style="width:100%; padding:.75rem; border:1px solid #d1d5db; border-radius:6px; font-size: 0.9rem;" required />
                        </div>
                        <div>
                            <label style="display:block; font-weight:600; margin-bottom: .5rem; color: #374151;">Category</label>
                            <select id="rCategory" name="category" style="width:100%; padding:.75rem; border:1px solid #d1d5db; border-radius:6px; font-size: 0.9rem;">
                                <option value="">Select Category</option>
                                <option>Antibiotics</option>
                                <option>Cardiology</option>
                                <option>Endocrine</option>
                                <option>OTC</option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block; font-weight:600; margin-bottom: .5rem; color: #374151;">Quantity *</label>
                            <input id="rQty" name="quantity" type="number" min="1" placeholder="e.g., 100" style="width:100%; padding:.75rem; border:1px solid #d1d5db; border-radius:6px; font-size: 0.9rem;" required />
                        </div>
                        <div>
                            <label style="display:block; font-weight:600; margin-bottom: .5rem; color: #374151;">Unit</label>
                            <select id="rUnit" name="unit" style="width:100%; padding:.75rem; border:1px solid #d1d5db; border-radius:6px; font-size: 0.9rem;">
                                <option>Tablets</option>
                                <option>Capsules</option>
                                <option>ml</option>
                                <option>mg</option>
                                <option>Bottles</option>
                            </select>
                        </div>
                        <div>
                            <label style="display:block; font-weight:600; margin-bottom: .5rem; color: #374151;">Batch/Lot Number</label>
                            <input id="rLot" name="batch_number" type="text" placeholder="e.g., LOT-2025-09A" style="width:100%; padding:.75rem; border:1px solid #d1d5db; border-radius:6px; font-size: 0.9rem;" />
                        </div>
                        <div>
                            <label style="display:block; font-weight:600; margin-bottom: .5rem; color: #374151;">Expiry Date</label>
                            <input id="rExp" name="expiry_date" type="date" style="width:100%; padding:.75rem; border:1px solid #d1d5db; border-radius:6px; font-size: 0.9rem;" />
                        </div>
                        <div>
                            <label style="display:block; font-weight:600; margin-bottom: .5rem; color: #374151;">Unit Price *</label>
                            <input id="rPrice" name="unit_price" type="number" step="0.01" min="0" placeholder="e.g., 15.50" style="width:100%; padding:.75rem; border:1px solid #d1d5db; border-radius:6px; font-size: 0.9rem;" required />
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <button type="submit" class="btn btn-primary" style="background: #0369a1; border: none; padding: 0.75rem 2rem; border-radius: 8px; color: white; cursor: pointer; font-size: 0.9rem;">
                            <i class="fas fa-truck" style="margin-right: 0.5rem;"></i>Receive Stock
                        </button>
                    </div>
                </form>
            </div>

            <!-- Adjust Inventory Form -->
            <div style="background: white; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin-top: 2rem;">
                <div style="padding: 1.5rem; border-bottom: 1px solid #e5e7eb; background: #fef7ff;">
                    <h3 id="adjust" style="margin: 0; color: #7c3aed;">
                        <i class="fas fa-sliders-h" style="margin-right: 0.5rem;"></i>Adjust Inventory
                    </h3>
                </div>
                <form id="adjust-form" style="padding: 1.5rem;">
                    <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1rem; margin-bottom: 1.5rem;">
                        <div>
                            <label style="display:block; font-weight:600; margin-bottom: .5rem; color: #374151;">Item Code *</label>
                            <input id="aCode" name="item_code" type="text" placeholder="e.g., MED-001" style="width:100%; padding:.75rem; border:1px solid #d1d5db; border-radius:6px; font-size: 0.9rem;" required />
                        </div>
                        <div>
                            <label style="display:block; font-weight:600; margin-bottom: .5rem; color: #374151;">Adjustment (±) *</label>
                            <input id="aDelta" name="adjustment" type="number" placeholder="e.g., -5 or +10" style="width:100%; padding:.75rem; border:1px solid #d1d5db; border-radius:6px; font-size: 0.9rem;" required />
                        </div>
                        <div style="grid-column: span 2;">
                            <label style="display:block; font-weight:600; margin-bottom: .5rem; color: #374151;">Reason *</label>
                            <select id="aReason" name="reason" style="width:100%; padding:.75rem; border:1px solid #d1d5db; border-radius:6px; font-size: 0.9rem;" required>
                                <option value="">Select Reason</option>
                                <option value="Correction">Stock Correction</option>
                                <option value="Damage">Damaged Goods</option>
                                <option value="Loss">Lost/Stolen</option>
                                <option value="Donation">Donation/Transfer</option>
                                <option value="Expiry">Expired Items</option>
                                <option value="Other">Other</option>
                            </select>
                        </div>
                    </div>
                    <div style="text-align: right;">
                        <button type="submit" class="btn btn-warning" style="background: #f59e0b; border: none; padding: 0.75rem 2rem; border-radius: 8px; color: white; cursor: pointer; font-size: 0.9rem;">
                            <i class="fas fa-save" style="margin-right: 0.5rem;"></i>Apply Adjustment
                        </button>
                    </div>
                </form>
            </div>

        </main>
    </div>

    <script>
        function handleLogout(){ if(confirm('Are you sure you want to logout?')) window.location.href = '<?= base_url('logout') ?>'; }

        // Navigation function for section buttons
        function showSection(sectionId) {
            // Scroll to the section
            document.getElementById(sectionId).scrollIntoView({ behavior: 'smooth' });

            // Update button states
            document.querySelectorAll('.btn').forEach(btn => {
                btn.classList.remove('active');
            });
            event.target.classList.add('active');
        }

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

        // Quick action functions for table buttons
        function receiveStock(itemCode) {
            document.getElementById('rCode').value = itemCode;
            document.getElementById('rCode').focus();
            document.getElementById('receive').scrollIntoView({ behavior: 'smooth' });
        }

        function adjustInventory(itemCode) {
            document.getElementById('aCode').value = itemCode;
            document.getElementById('aCode').focus();
            document.getElementById('adjust').scrollIntoView({ behavior: 'smooth' });
        }

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
