<?php $__env->startSection('title'); ?>
    <?php echo e(__('Third-Party APIs')); ?>

<?php $__env->stopSection(); ?>


<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <?php echo e(__('Third-Party APIs')); ?>

            </h3>
        </div>
        <div class="row grid-margin">
            <div class="col-lg-12">
                <div class="card">
                    <div class="custom-card-body">
                        <form id="formdata" class="create-form-without-reset" action="<?php echo e(route('school-settings.third-party.update')); ?>" method="POST" novalidate="novalidate" enctype="multipart/form-data">
                            <?php echo csrf_field(); ?>
                            
                            <div class="border border-secondary rounded-lg my-4 mx-1">
                                <div class="col-md-12 mt-3">
                                    <h4><?php echo e(__('google_recaptcha')); ?></h4>
                                </div>
                                <div class="col-12 mb-3">
                                    <hr class="mt-0">
                                </div>
                                <div class="row my-4 mx-1">
                                    <div class="form-group col-md-4 col-sm-12">
                                        <label for="RECAPTCHA_SITE_KEY"><?php echo e(__('RECAPTCHA_SITE_KEY')); ?></label>
                                        <input name="SCHOOL_RECAPTCHA_SITE_KEY" id="RECAPTCHA_SITE_KEY" value="<?php echo e($schoolSettings['SCHOOL_RECAPTCHA_SITE_KEY'] ?? ''); ?>" type="text" placeholder="<?php echo e(__('RECAPTCHA_SITE_KEY')); ?>" class="form-control"/>
                                    </div>

                                    <div class="form-group col-md-4 col-sm-12">
                                        <label for="RECAPTCHA_SECRET_KEY"><?php echo e(__('RECAPTCHA_SECRET_KEY')); ?></label>
                                        <input name="SCHOOL_RECAPTCHA_SECRET_KEY" id="RECAPTCHA_SECRET_KEY" value="<?php echo e($schoolSettings['SCHOOL_RECAPTCHA_SECRET_KEY'] ?? ''); ?>" type="text" placeholder="<?php echo e(__('RECAPTCHA_SECRET_KEY')); ?>" class="form-control"/>
                                    </div>
    
                                </div>

                                

                            </div>
                            

                            
                            <div class="border border-secondary rounded-lg my-4 mx-1">
                                <div class="col-md-12 mt-3">
                                    <h4><?php echo e(__('WhatsApp Notifications')); ?></h4>
                                </div>
                                <div class="col-12 mb-3">
                                    <hr class="mt-0">
                                </div>
                                <div class="row my-4 mx-1">
                                    <div class="form-group col-md-4 col-sm-12">
                                        <label for="whatsapp_status"><?php echo e(__('Enable WhatsApp Notifications')); ?></label>
                                        <select name="whatsapp_status" id="whatsapp_status" class="form-control">
                                            <option value="0" <?php echo e(($schoolSettings['whatsapp_status'] ?? 1) == 0 ? 'selected' : ''); ?>><?php echo e(__('Disabled')); ?></option>
                                            <option value="1" <?php echo e(($schoolSettings['whatsapp_status'] ?? 1) == 1 ? 'selected' : ''); ?>><?php echo e(__('Enabled')); ?></option>
                                        </select>
                                    </div>
                                </div>

                                <div id="whatsapp_connection_section" class="row my-4 mx-1" style="display: <?php echo e(($schoolSettings['whatsapp_status'] ?? 1) == 1 ? 'flex' : 'none'); ?>; align-items: center;">
                                    <div class="col-md-12 mb-3">
                                        <h5><?php echo e(__('WhatsApp Connection Status')); ?></h5>
                                        <div id="whatsapp_status_text" class="mb-3 font-weight-bold text-info"><i class="fa fa-spinner fa-spin"></i> Connecting to WhatsApp service...</div>
                                        
                                        <div id="whatsapp_qr_container" class="mb-3" style="display:none;">
                                            <img id="whatsapp_qr" src="" alt="WhatsApp QR Code" style="border: 1px solid #ccc; padding: 10px; max-width: 250px;" />
                                            <p class="text-muted mt-2">Open WhatsApp on your phone -> Linked Devices -> Link a Device, and scan this QR code.</p>
                                        </div>

                                        <button id="whatsapp_disconnect_btn" type="button" class="btn btn-danger" style="display:none;"><?php echo e(__('Disconnect WhatsApp')); ?></button>
                                    </div>
                                </div>
                            </div>
                            

                            <input class="btn btn-theme float-right ml-3" id="create-btn" type="submit" value=<?php echo e(__('submit')); ?>>
                            <input class="btn btn-secondary float-right" type="reset" value=<?php echo e(__('reset')); ?>>
                        </form>

<script src="https://cdn.socket.io/4.7.2/socket.io.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        const statusSelect = document.getElementById('whatsapp_status');
        const connSection = document.getElementById('whatsapp_connection_section');
        const statusText = document.getElementById('whatsapp_status_text');
        const qrContainer = document.getElementById('whatsapp_qr_container');
        const qrImg = document.getElementById('whatsapp_qr');
        const disconnectBtn = document.getElementById('whatsapp_disconnect_btn');

        statusSelect.addEventListener('change', function() {
            if (this.value == '1') {
                connSection.style.display = 'flex';
                initWhatsAppSocket();
                startStatusPolling();
            } else {
                connSection.style.display = 'none';
                stopStatusPolling();
                if (window.whatsappSocket) {
                    window.whatsappSocket.disconnect();
                }
            }
        });

        let socketInitialized = false;

        function initWhatsAppSocket() {
            if (socketInitialized) return;
            
            const socketUrl = "https://sms.tehub.in";
            const userId = "school_<?php echo e(Auth::user()->school_id); ?>";

            console.log('Connecting to WhatsApp socket for User ID:', userId);
            const socket = io(socketUrl);
            window.whatsappSocket = socket;
            socketInitialized = true;

            socket.on('connect', () => {
                statusText.innerHTML = '<span class="text-info"><i class="fa fa-spinner fa-spin"></i> Connected to service. Requesting session...</span>';
                socket.emit('join', userId);
            });

            socket.on('qr', (url) => {
                statusText.innerHTML = '<span class="text-warning"><i class="fa fa-qrcode"></i> Scan the QR code below to connect your WhatsApp account</span>';
                qrImg.src = url;
                qrContainer.style.display = 'block';
                disconnectBtn.style.display = 'none';
            });

            socket.on('ready', (data) => {
                let phone = data.phone ? ' (+' + data.phone + ')' : '';
                statusText.innerHTML = '<span class="text-success"><i class="fa fa-check-circle"></i> WhatsApp Connected and Active!' + phone + '</span>';
                qrContainer.style.display = 'none';
                disconnectBtn.style.display = 'inline-block';
            });

            socket.on('disconnected', () => {
                statusText.innerHTML = '<span class="text-danger"><i class="fa fa-times-circle"></i> Disconnected / Session Closed</span>';
                qrContainer.style.display = 'none';
                disconnectBtn.style.display = 'none';
                socketInitialized = false;
                setTimeout(initWhatsAppSocket, 5000);
            });

            socket.on('error', (msg) => {
                statusText.innerHTML = '<span class="text-danger"><i class="fa fa-exclamation-triangle"></i> Error: ' + msg + '</span>';
            });

            socket.on('connect_error', (err) => {
                console.error('Socket connection error:', err.message);
                statusText.innerHTML = '<span class="text-warning"><i class="fa fa-spinner fa-spin"></i> Reconnecting to WhatsApp service...</span>';
            });
            
            disconnectBtn.addEventListener('click', function() {
                if (confirm('Are you sure you want to disconnect this WhatsApp session?')) {
                    statusText.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Disconnecting...';
                    fetch('https://sms.tehub.in/api/auth/logout-whatsapp', {
                        method: 'POST',
                        headers: {
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({ userId: userId })
                    })
                    .then(res => res.json())
                    .then(data => {
                        if (data.success) {
                            statusText.innerHTML = '<span class="text-danger"><i class="fa fa-sign-out"></i> Logged out successfully.</span>';
                            qrContainer.style.display = 'none';
                            disconnectBtn.style.display = 'none';
                        }
                    })
                    .catch(err => {
                        console.error('Logout error:', err);
                    });
                }
            });
        }

        let pollingInterval = null;

        function startStatusPolling() {
            if (pollingInterval) return;
            
            const userId = "school_<?php echo e(Auth::user()->school_id); ?>";
            const statusUrl = `https://sms.tehub.in/api/auth/whatsapp-status?userId=${userId}`;

            console.log('Starting WhatsApp status HTTP polling...');

            function checkStatus() {
                fetch(statusUrl)
                    .then(res => res.json())
                    .then(data => {
                        if (data.status === 'ready') {
                            let phone = data.phone ? ' (+' + data.phone + ')' : '';
                            statusText.innerHTML = '<span class="text-success"><i class="fa fa-check-circle"></i> WhatsApp Connected and Active!' + phone + '</span>';
                            qrContainer.style.display = 'none';
                            disconnectBtn.style.display = 'inline-block';
                        } else if (data.status === 'qr' && data.qr) {
                            statusText.innerHTML = '<span class="text-warning"><i class="fa fa-qrcode"></i> Scan the QR code below to connect your WhatsApp account</span>';
                            qrImg.src = data.qr;
                            qrContainer.style.display = 'block';
                            disconnectBtn.style.display = 'none';
                        } else if (data.status === 'connecting') {
                            statusText.innerHTML = '<span class="text-info"><i class="fa fa-spinner fa-spin"></i> Connecting to WhatsApp service... Please wait.</span>';
                        }
                    })
                    .catch(err => {
                        console.error('Status polling error:', err);
                        statusText.innerHTML = '<span class="text-danger"><i class="fa fa-exclamation-triangle"></i> Cannot reach WhatsApp service. Retrying...</span>';
                    });
            }

            // Fire immediately, then every 3 seconds
            checkStatus();
            pollingInterval = setInterval(checkStatus, 3000);
        }

        function stopStatusPolling() {
            if (pollingInterval) {
                clearInterval(pollingInterval);
                pollingInterval = null;
            }
        }

        if (statusSelect.value == '1') {
            initWhatsAppSocket();
            startStatusPolling();
        }
    });
</script>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/school-settings/third-party-apis.blade.php ENDPATH**/ ?>