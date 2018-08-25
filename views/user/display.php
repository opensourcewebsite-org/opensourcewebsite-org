<div class="info-box bg-info">
    <span class="info-box-icon"><i class="fa fa-users"></i></span>
    <div class="info-box-content">
        <span class="info-box-text">Registered Users</span>
        <span class="info-box-number"><?php echo $registered_users; ?></span>
        <div class="progress">
            <div class="progress-bar" style="width: <?php echo $percentage; ?>%"></div>
        </div>
        <span class="progress-description">
            <?php echo $confirmed_users . ' confirmed out of ' . $registered_users; ?>
        </span>
    </div>
</div>