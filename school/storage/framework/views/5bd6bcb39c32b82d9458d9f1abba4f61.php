<?php $attributes ??= new \Illuminate\View\ComponentAttributeBag; ?>
<?php foreach($attributes->onlyProps([
    'id' => 'helpModal',
    'role' => null,
    'module' => null,
    'title' => null,
    'steps' => []
]) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $attributes = $attributes->exceptProps([
    'id' => 'helpModal',
    'role' => null,
    'module' => null,
    'title' => null,
    'steps' => []
]); ?>
<?php foreach (array_filter(([
    'id' => 'helpModal',
    'role' => null,
    'module' => null,
    'title' => null,
    'steps' => []
]), 'is_string', ARRAY_FILTER_USE_KEY) as $__key => $__value) {
    $$__key = $$__key ?? $__value;
} ?>
<?php $__defined_vars = get_defined_vars(); ?>
<?php foreach ($attributes as $__key => $__value) {
    if (array_key_exists($__key, $__defined_vars)) unset($$__key);
} ?>
<?php unset($__defined_vars); ?>

<?php
    $guideService = app(App\Services\UserGuideService::class);
    $moduleData = null;
    $moduleSteps = [];
    
    if ($role && $module) {
        $roleData = $guideService->getModuleGuide($role);
        if ($roleData && isset($roleData[$module])) {
            $moduleSteps = $roleData[$module];
            $title = $title ?? str_replace('_', ' ', $module) ?? __('Help Guide');
            $steps = !empty($steps) ? $steps : $moduleSteps;
        }
    }
?>

<div class="modal fade help-modal" id="<?php echo e($id); ?>" tabindex="-1" role="dialog" aria-labelledby="<?php echo e($id); ?>Label" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header help-modal-header">
                <div>
                    <h5 class="modal-title" id="<?php echo e($id); ?>Label">
                        <i class="mdi mdi-help-circle-outline me-2"></i>
                        <?php echo e(__($title)); ?>

                    </h5>
                </div>
                <button type="button" class="close close-modal" data-dismiss="modal" aria-label="Close">
                    <i class="mdi mdi-close"></i>
                </button>
            </div>
            <div class="modal-body">
                <div class="steps-guide">
                    <?php if(!empty($steps)): ?>
                        <?php $__currentLoopData = $steps; $__env->addLoop($__currentLoopData); foreach($__currentLoopData as $step): $__env->incrementLoopIndices(); $loop = $__env->getLastLoop(); ?>
                            <div class="step d-flex align-items-start <?php echo e(!$loop->last ? 'mb-4' : ''); ?>">
                                <div class="step-icon-wrapper mr-3">
                                    <i class="<?php echo e($step['icon']); ?> step-icon mdi-24px"></i>
                                    <span class="step-number badge rounded-circle d-flex align-items-center justify-content-center">
                                        <?php echo e($step['step']); ?>

                                    </span>
                                </div>
                                <div class="step-content">
                                    <h6 class="step-title mb-1"><?php echo e($step['title']); ?></h6>
                                    <p class="step-description mb-0"><?php echo e($step['description']); ?></p>
                                </div>
                            </div>
                        <?php endforeach; $__env->popLoop(); $loop = $__env->getLastLoop(); ?>
                    <?php else: ?>
                        <div class="alert alert-info">
                            <?php echo e(__('No guide steps available for this module.')); ?>

                        </div>
                    <?php endif; ?>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary close-modal" data-dismiss="modal">
                    <i class="mdi mdi-close-circle me-1"></i>
                    <?php echo e(__('close')); ?>

                </button>
            </div>
        </div>
    </div>
</div>

<style>

</style><?php /**PATH /home/shacartc/school.tehub.in/resources/views/components/help-modal.blade.php ENDPATH**/ ?>