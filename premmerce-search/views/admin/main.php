<?php if(!defined('WPINC')) die; ?>

<div class="wrap">
    <h2><?php _e('Premmerce Search', 'premmerce-search') ?></h2>
    <h2 class="nav-tab-wrapper">
        <?php foreach($tabs as $tab => $name): ?>
        <?php
                $class = ($tab == $current)? ' nav-tab-active' : '';
                $link = ('affiliate' == $tab) ? '?page=premmerce-search-admin-affiliation' : '?page=premmerce-search-admin&tab=' . $tab;
            ?>
        <a class='nav-tab<?php echo $class; ?>' href='<?php echo $link; ?>'>
            <?php echo $name; ?>
        </a>
        <?php endforeach; ?>

        <?php if (!premmerce_ps_fs()->can_use_premium_code()) : //if it is not Premium plan.?>
        <a class="nav-tab premmerce-upgrate-to-premium-button"
            href="<?php echo admin_url('admin.php?page=premmerce-search-admin-pricing'); ?>">
            <?php _e('Upgrate to Premium', 'premmerce-search') ?>
        </a>
        <?php endif; ?>
    </h2>
    <?php $file = __DIR__ . "/tabs/{$current}.php" ?>
    <?php if(file_exists($file)): ?>
    <?php include $file ?>
    <?php endif; ?>
</div>
