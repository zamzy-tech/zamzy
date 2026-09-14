<?php $__env->startSection('title'); ?>
    <?php echo e(__('web_settings')); ?>

<?php $__env->stopSection(); ?>

<?php $__env->startSection('content'); ?>
    <div class="content-wrapper">
        <div class="page-header">
            <h3 class="page-title">
                <?php echo e(__('manage')); ?> <?php echo e(__('web_settings')); ?>

            </h3>
        </div>

        <div class="row">
            <div class="col-lg-12 grid-margin stretch-card">
                <div class="card">
                    <div class="card-body">
                        <form class="create-form-without-reset" id="formdata" action="<?php echo e(route('web-settings.store')); ?>" enctype="multipart/form-data" method="POST" novalidate="novalidate">
                            <?php echo csrf_field(); ?>

                            
                            <div class="border border-secondary rounded-lg my-4 mx-1">
                                <div class="col-md-12 mt-3">
                                    <h4><?php echo e(__('colour_settings')); ?></h4>
                                </div>
                                <div class="col-12 mb-3">
                                    <hr class="mt-0">
                                </div>
                                <div class="row my-4 mx-1">
                                    
                                    <div class="form-group col-md-3 col-sm-12">
                                        <label for="theme_primary_color"><?php echo e(__('theme_primary_color')); ?> <span class="text-danger">*</span></label>
                                        <input name="theme_primary_color" id="theme_primary_color"
                                            value="<?php echo e($settings['theme_primary_color'] ?? ''); ?>" type="text" required
                                            placeholder="<?php echo e(__('theme_primary_color')); ?>" class="theme_primary_color color-picker" />
                                        <small>
                                            <a href="javascript:null" onclick="restore_default_color(1)"><?php echo e(__('restore_default')); ?></a>
                                        </small>
                                    </div>
                                    
                                    <div class="form-group col-md-3 col-sm-12">
                                        <label for="theme_secondary_color"><?php echo e(__('theme_secondary_color')); ?> <span class="text-danger">*</span></label>
                                        <input name="theme_secondary_color" id="theme_secondary_color"
                                            value="<?php echo e($settings['theme_secondary_color'] ?? ''); ?>" type="text" required
                                            placeholder="<?php echo e(__('theme_secondary_color')); ?>" class="theme_secondary_color color-picker" />
                                        <small>
                                            <a href="javascript:null" onclick="restore_default_color(2)"><?php echo e(__('restore_default')); ?></a>
                                        </small>
                                    </div>
                                    
                                    <div class="form-group col-md-3 col-sm-12">
                                        <label for="theme_secondary_color_1"><?php echo e(__('theme_secondary_color_1')); ?> <span class="text-danger">*</span></label>
                                        <input name="theme_secondary_color_1" id="theme_secondary_color_1"
                                            value="<?php echo e($settings['theme_secondary_color_1'] ?? ''); ?>" type="text" required
                                            placeholder="<?php echo e(__('theme_secondary_color_1')); ?>" class="theme_secondary_color_1 color-picker" />
                                        <small>
                                            <a href="javascript:null" onclick="restore_default_color(3)"><?php echo e(__('restore_default')); ?></a>
                                        </small>
                                    </div>
                                    
                                    <div class="form-group col-md-3 col-sm-12">
                                        <label for="theme_primary_background_color"><?php echo e(__('theme_primary_background_color')); ?> <span class="text-danger">*</span></label>
                                        <input name="theme_primary_background_color" id="theme_primary_background_color"
                                            value="<?php echo e($settings['theme_primary_background_color'] ?? ''); ?>" type="text" required
                                            placeholder="<?php echo e(__('theme_primary_background_color')); ?>" class="theme_primary_background_color color-picker" />
                                        <small>
                                            <a href="javascript:null" onclick="restore_default_color(4)"><?php echo e(__('restore_default')); ?></a>
                                        </small>
                                    </div>
                                    
                                    <div class="form-group col-md-3 col-sm-12">
                                        <label for="theme_text_secondary_color"><?php echo e(__('theme_text_secondary_color')); ?> <span class="text-danger">*</span></label>
                                        <input name="theme_text_secondary_color" id="theme_text_secondary_color"
                                            value="<?php echo e($settings['theme_text_secondary_color'] ?? ''); ?>" type="text" required
                                            placeholder="<?php echo e(__('theme_text_secondary_color')); ?>" class="theme_text_secondary_color color-picker" />
                                        <small>
                                            <a href="javascript:null" onclick="restore_default_color(5)"><?php echo e(__('restore_default')); ?></a>
                                        </small>
                                    </div>
                                </div>
                            </div>
                            

                            
                            <div class="border border-secondary rounded-lg my-4 mx-1">
                                <div class="col-md-12 mt-3">
                                    <h4><?php echo e(__('general_settings')); ?></h4>
                                </div>
                                <div class="col-12 mb-3">
                                    <hr class="mt-0">
                                </div>
                                <div class="row my-4 mx-1">

                                    <div class="form-group col-sm-12 col-md-6">
                                        <label for="image"><?php echo e(__('hero_image')); ?> </label>
                                        <input type="file" name="home_image" class="file-upload-default" />
                                        <div class="input-group col-xs-12">
                                            <input type="text" class="form-control file-upload-info"
                                                id="home_image" disabled=""
                                                placeholder="<?php echo e(__('home_image')); ?>" />
                                            <span class="input-group-append">
                                                <button class="file-upload-browse btn btn-theme"
                                                    type="button"><?php echo e(__('upload')); ?></button>
                                            </span>
                                            <div class="col-md-12 mt-2">
                                                <img height="50px" src='<?php echo e($settings['home_image'] ?? asset('assets/landing_page_images/heroImg.png')); ?>'
                                                    alt="">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-md-6 col-sm-12">
                                        <label for="hero_title_1"><?php echo e(__('hero_title_1')); ?></label>
                                        <input name="hero_title_1" id="hero_title_1" value="<?php echo e($settings['hero_title_1'] ?? ''); ?>" type="text" placeholder="<?php echo e(__('hero_title_1')); ?>" class="form-control" maxlength="200" />
                                    </div>

                                    <div class="form-group col-md-4 col-sm-12">
                                        <label for="hero_title_2"><?php echo e(__('hero_title_2')); ?></label>
                                        <input name="hero_title_2" id="hero_title_2" value="<?php echo e($settings['hero_title_2'] ?? ''); ?>" type="text" placeholder="<?php echo e(__('hero_title_2')); ?>" class="form-control" maxlength="50"/>
                                    </div>
                                    <div class="form-group col-sm-12 col-md-4">
                                        <label for="image"><?php echo e(__('hero_image_2')); ?> </label>
                                        <input type="file" name="hero_title_2_image" class="file-upload-default" />
                                        <div class="input-group col-xs-12">
                                            <input type="text" class="form-control file-upload-info"
                                                id="hero_title_2_image" disabled=""
                                                placeholder="<?php echo e(__('hero_title_2_image')); ?>" />
                                            <span class="input-group-append">
                                                <button class="file-upload-browse btn btn-theme"
                                                    type="button"><?php echo e(__('upload')); ?></button>
                                            </span>
                                            <div class="col-md-12 mt-2">
                                                <img height="50px" src='<?php echo e($settings['hero_title_2_image'] ?? asset('assets/landing_page_images/user.png')); ?>'
                                                    alt="">
                                            </div>
                                        </div>
                                    </div>

                                    <div class="col-md-4">

                                    </div>

                                    <div class="form-group col-sm-12 col-md-4">
                                        <div class="d-flex">
                                            <div class="form-check w-fit-content ml-3">
                                                <label class="form-check-label ml-4">
                                                    <?php if(isset($settings['display_school_logos'])): ?>
                                                        <input type="checkbox" class="form-check-input" name="display_school_logos" value="1" <?php echo e($settings['display_school_logos'] ? 'checked' : ''); ?>><?php echo e(__('display_school_logos')); ?>

                                                    <?php else: ?>
                                                        <input type="checkbox" class="form-check-input" name="display_school_logos" value="1" checked><?php echo e(__('display_school_logos')); ?>

                                                    <?php endif; ?>
                                                    
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="form-group col-sm-12 col-md-4">
                                        <div class="d-flex">
                                            <div class="form-check w-fit-content ml-3">
                                                <label class="form-check-label ml-4">
                                                    <?php if(isset($settings['display_counters'])): ?>
                                                        <input type="checkbox" class="form-check-input" name="display_counters" value="1" <?php echo e($settings['display_counters'] ? 'checked' : ''); ?>><?php echo e(__('display_counters')); ?>

                                                    <?php else: ?>
                                                        <input type="checkbox" class="form-check-input" name="display_counters" value="1" checked><?php echo e(__('display_counters')); ?>

                                                    <?php endif; ?>
                                                    
                                                </label>
                                            </div>
                                        </div>
                                    </div>

                                </div>
                            </div>

                            <div class="border border-secondary rounded-lg my-4 mx-1">
                                <div class="col-md-12 mt-3">
                                    <h4><?php echo e(__('about_us')); ?></h4>
                                </div>
                                <div class="col-12 mb-3">
                                    <hr class="mt-0">
                                </div>
                                <div class="row my-4 mx-1">
                                    <div class="form-group col-sm-12 col-md-6">
                                        <label for="title"><?php echo e(__('title')); ?> <span class="text-danger">*</span></label>
                                        <?php echo Form::text('about_us_title', $settings['about_us_title'] ?? null, ['required','class' => 'form-control','placeholder' => __('title')]); ?>

                                    </div>
                                    <div class="form-group col-sm-12 col-md-6">
                                        <label for="heading"><?php echo e(__('heading')); ?> <span class="text-danger">*</span></label>
                                        <?php echo Form::text('about_us_heading', $settings['about_us_heading'] ?? null, ['required','class' => 'form-control', 'placeholder' => __('heading')]); ?>

                                    </div>

                                    <div class="form-group col-sm-12 col-md-12">
                                        <label for="description"><?php echo e(__('description')); ?> <span class="text-danger">*</span></label>
                                        <?php echo Form::textarea('about_us_description', $settings['about_us_description'] ?? null, ['required','class' => 'form-control','rows' => 5, 'placeholder' => __('description')]); ?>

                                    </div>

                                    <div class="form-group col-sm-12 col-md-12">
                                        <label for="points"><?php echo e(__('points')); ?> <span class="text-danger">*</span> <span class="text-small text-info">(<?php echo e(__('please_use_commas_or_press_enter_to_add_multiple_points')); ?>)</label>
                                        <?php echo Form::text('about_us_points', $settings['about_us_points'] ?? null, ['required','class' => 'form-control', 'id' => 'tags', 'placeholder' => __('about_us_points')]); ?>

                                    </div>

                                    <div class="form-group col-sm-12 col-md-4">
                                        <label for="image"><?php echo e(__('image')); ?> </label>
                                        <input type="file" name="about_us_image" class="file-upload-default" />
                                        <div class="input-group col-xs-12">
                                            <input type="text" class="form-control file-upload-info"
                                                id="about_us_image" disabled=""
                                                placeholder="<?php echo e(__('image')); ?>" />
                                            <span class="input-group-append">
                                                <button class="file-upload-browse btn btn-theme"
                                                    type="button"><?php echo e(__('upload')); ?></button>
                                            </span>
                                            <div class="col-md-12 mt-2">
                                                <img height="50px" src='<?php echo e($settings['about_us_image'] ?? asset('assets/landing_page_images/whyBestImg.png')); ?>'
                                                    alt="">
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="border border-secondary rounded-lg my-4 mx-1">
                                <div class="col-md-12 mt-3">
                                    <h4><?php echo e(__('custom_package_section')); ?></h4>
                                </div>
                                <div class="col-12 mb-3">
                                    <hr class="mt-0">
                                </div>
                                <div class="row my-4 mx-1">
                                    <div class="form-group col-sm-12 col-md-12">
                                        <label><?php echo e(__('status')); ?> <span class="text-danger">*</span></label>
                                        <div class="d-flex">
                                            <?php if(isset($settings['custom_package_status']) && $settings['custom_package_status'] == 1): ?>
                                                <div class="form-check form-check-inline">
                                                    <label class="form-check-label">
                                                        <input type="radio" class="form-check-input" name="custom_package_status" id="enable" checked value="1"><?php echo e(__("enable")); ?> </label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <label class="form-check-label">
                                                        <input type="radio" class="form-check-input" name="custom_package_status" id="disable" value="0"><?php echo e(__("disable")); ?>

                                                    </label>
                                                </div>
                                            <?php else: ?>
                                                <div class="form-check form-check-inline">
                                                    <label class="form-check-label">
                                                        <input type="radio" class="form-check-input" name="custom_package_status" id="enable" value="1"><?php echo e(__("enable")); ?> </label>
                                                </div>
                                                <div class="form-check form-check-inline">
                                                    <label class="form-check-label">
                                                        <input type="radio" class="form-check-input" name="custom_package_status" checked id="disable" value="0"><?php echo e(__("disable")); ?>

                                                    </label>
                                                </div>
                                            <?php endif; ?>
                                            
                                        </div>
                                    </div>
                                    <div class="form-group col-sm-12 col-md-12">
                                        <label for="description"><?php echo e(__('description')); ?> </label>
                                        <?php echo Form::textarea('custom_package_description', $settings['custom_package_description'] ?? null, ['class' => 'form-control','rows' => 5, 'placeholder' => __('description')]); ?>

                                    </div>
                                </div>
                            </div>

                            <div class="border border-secondary rounded-lg my-4 mx-1">
                                <div class="col-md-12 mt-3">
                                    <h4><?php echo e(__('download_our_app_section')); ?></h4>
                                </div>
                                <div class="col-12 mb-3">
                                    <hr class="mt-0">
                                </div>
                                <div class="form-group col-sm-12 col-md-4">
                                    <label for="image"><?php echo e(__('image')); ?> </label>
                                    <input type="file" name="download_our_app_image" class="file-upload-default" />
                                    <div class="input-group col-xs-12">
                                        <input type="text" class="form-control file-upload-info"
                                            id="download_our_app_image" disabled=""
                                            placeholder="<?php echo e(__('image')); ?>" />
                                        <span class="input-group-append">
                                            <button class="file-upload-browse btn btn-theme"
                                                type="button"><?php echo e(__('upload')); ?></button>
                                        </span>
                                        <div class="col-md-12 mt-2">
                                            <img height="50px" src='<?php echo e($settings['download_our_app_image'] ?? asset('assets/landing_page_images/ourApp.png')); ?>'
                                                alt="">
                                        </div>
                                    </div>
                                </div>
                                <div class="row my-4 mx-1">
                                    <div class="form-group col-sm-12 col-md-12">
                                        <label for="description"><?php echo e(__('description')); ?> <span class="text-danger">*</span></label>
                                        <?php echo Form::textarea('download_our_app_description', $settings['download_our_app_description'] ?? null, ['required','class' => 'form-control','rows' => 5, 'placeholder' => __('description')]); ?>

                                    </div>
                                </div>
                            </div>

                            <div class="border border-secondary rounded-lg my-4 mx-1">
                                <div class="col-md-12 mt-3">
                                    <h4><?php echo e(__('social_media_links')); ?></h4>
                                </div>
                                <div class="col-12 mb-3">
                                    <hr class="mt-0">
                                </div>
                                <div class="row my-4 mx-1">
                                    
                                    <div class="col-md-4 col-sm-12 border-right">
                                        <div class="form-group">
                                            <label for="facebook_name"><?php echo e(__('facebook')); ?> <?php echo e(__('name')); ?></label>
                                            <input name="facebook_name" id="facebook_name" value="<?php echo e($settings['facebook_name'] ?? 'Facebook'); ?>" type="text" placeholder="Facebook" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <label for="facebook"><?php echo e(__('facebook')); ?> <?php echo e(__('link')); ?></label>
                                            <input name="facebook" id="facebook" value="<?php echo e($settings['facebook'] ?? ''); ?>" type="text" placeholder="URL" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <label for="facebook_icon"><?php echo e(__('facebook')); ?> <?php echo e(__('icon_class')); ?></label>
                                            <input name="facebook_icon" id="facebook_icon" value="<?php echo e($settings['facebook_icon'] ?? 'fab fa-facebook'); ?>" type="text" placeholder="e.g. fab fa-facebook" class="form-control" />
                                        </div>
                                    </div>

                                    
                                    <div class="col-md-4 col-sm-12 border-right">
                                        <div class="form-group">
                                            <label for="instagram_name"><?php echo e(__('instagram')); ?> <?php echo e(__('name')); ?></label>
                                            <input name="instagram_name" id="instagram_name" value="<?php echo e($settings['instagram_name'] ?? 'Instagram'); ?>" type="text" placeholder="Instagram" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <label for="instagram"><?php echo e(__('instagram')); ?> <?php echo e(__('link')); ?></label>
                                            <input name="instagram" id="instagram" value="<?php echo e($settings['instagram'] ?? ''); ?>" type="text" placeholder="URL" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <label for="instagram_icon"><?php echo e(__('instagram')); ?> <?php echo e(__('icon_class')); ?></label>
                                            <input name="instagram_icon" id="instagram_icon" value="<?php echo e($settings['instagram_icon'] ?? 'fab fa-instagram'); ?>" type="text" placeholder="e.g. fab fa-instagram" class="form-control" />
                                        </div>
                                    </div>

                                    
                                    <div class="col-md-4 col-sm-12">
                                        <div class="form-group">
                                            <label for="linkedin_name"><?php echo e(__('linkedin')); ?> <?php echo e(__('name')); ?></label>
                                            <input name="linkedin_name" id="linkedin_name" value="<?php echo e($settings['linkedin_name'] ?? 'LinkedIn'); ?>" type="text" placeholder="LinkedIn" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <label for="linkedin"><?php echo e(__('linkedin')); ?> <?php echo e(__('link')); ?></label>
                                            <input name="linkedin" id="linkedin" value="<?php echo e($settings['linkedin'] ?? ''); ?>" type="text" placeholder="URL" class="form-control" />
                                        </div>
                                        <div class="form-group">
                                            <label for="linkedin_icon"><?php echo e(__('linkedin')); ?> <?php echo e(__('icon_class')); ?></label>
                                            <input name="linkedin_icon" id="linkedin_icon" value="<?php echo e($settings['linkedin_icon'] ?? 'fab fa-linkedin'); ?>" type="text" placeholder="e.g. fab fa-linkedin" class="form-control" />
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div class="border border-secondary rounded-lg my-4 mx-1">
                                <div class="col-md-12 mt-3">
                                    <h4><?php echo e(__('Footer Settings')); ?></h4>
                                </div>
                                <div class="col-12 mb-3">
                                    <hr class="mt-0">
                                </div>
                                <div class="row my-4 mx-1">
                                    <div class="form-group col-sm-12 col-md-12">
                                        <label for=""><?php echo e(__('short_description')); ?></label>
                                        <textarea name="short_description" class="form-control" id="short_description" required placeholder="<?php echo e(__('short_description')); ?>"><?php echo e($settings['short_description'] ?? ''); ?></textarea>
                                    </div>

                                    <div class="form-group col-sm-12 col-md-12">
                                        <label for=""><?php echo e(__('footer_text')); ?></label>
                                        <textarea id="tinymce_message" name="footer_text" id="footer_text" required placeholder="<?php echo e(__('footer_text')); ?>"><?php echo e($settings['footer_text'] ?? ''); ?></textarea>
                                    </div>
                                </div>
                            </div>
                            <input class="btn btn-theme float-right ml-3" id="create-btn" type="submit" value=<?php echo e(__('submit')); ?>>
                            <input class="btn btn-secondary float-right" type="reset" value=<?php echo e(__('reset')); ?>>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
<?php $__env->stopSection(); ?>
<?php $__env->startSection('script'); ?>
    <script>
        function restore_default_color(value)
        {
            if (value == 1) {
                $('#theme_primary_color').val('#56CC99');
                $('.theme_primary_color').asColorPicker('val', '#56CC99');
            }

            if (value == 2) {
                $('#theme_secondary_color').val('#215679');
                $('.theme_secondary_color').asColorPicker('val', '#215679');
            }
            if (value == 3) {
                $('#theme_secondary_color_1').val('#38A3A5');
                $('.theme_secondary_color_1').asColorPicker('val', '#38A3A5');
            }
            if (value == 4) {
                $('#theme_primary_background_color').val('#F2F5F7');
                $('.theme_primary_background_color').asColorPicker('val', '#F2F5F7');
            }
            if (value == 5) {
                $('#theme_text_secondary_color').val('#5C788C');
                $('.theme_text_secondary_color').asColorPicker('val', '#5C788C');
            }
            
        }
    </script>
<?php $__env->stopSection(); ?>
<?php echo $__env->make('layouts.master', \Illuminate\Support\Arr::except(get_defined_vars(), ['__data', '__path']))->render(); ?><?php /**PATH /home/shacartc/school.tehub.in/resources/views/web_settings/index.blade.php ENDPATH**/ ?>