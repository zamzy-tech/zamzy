<?php defined('BASEPATH') or exit('No direct script access allowed'); ?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Secure Client Document Portal</title>
    <!-- Tailwind CSS (CDN for standalone premium layout) -->
    <script src="https://cdn.tailwindcss.com"></script>
    <style>
        body {
            background: linear-gradient(135deg, #0f172a 0%, #1e1b4b 100%);
            font-family: 'Inter', sans-serif;
        }
        .glass-card {
            background: rgba(255, 255, 255, 0.03);
            backdrop-filter: blur(12px);
            -webkit-backdrop-filter: blur(12px);
            border: 1px solid rgba(255, 255, 255, 0.08);
        }
    </style>
</head>
<body class="min-h-screen flex items-center justify-center p-4">
    <div class="w-full max-w-md glass-card rounded-2xl p-8 shadow-2xl relative overflow-hidden">
        <!-- Background light effect -->
        <div class="absolute -top-10 -right-10 w-40 h-40 bg-purple-600 rounded-full filter blur-3xl opacity-20"></div>
        <div class="absolute -bottom-10 -left-10 w-40 h-40 bg-blue-600 rounded-full filter blur-3xl opacity-20"></div>

        <div class="text-center mb-8 relative z-10">
            <h2 class="text-3xl font-extrabold text-white tracking-tight">Secure Upload Portal</h2>
            <p class="text-neutral-400 mt-2 text-sm">Please log in to upload your loan documents</p>
        </div>

        <?php if (!empty($error)) { ?>
            <div class="bg-red-500/10 border border-red-500/20 text-red-400 rounded-lg p-3 text-sm mb-6 text-center relative z-10">
                <?php echo e($error); ?>
            </div>
        <?php } ?>

        <form action="<?php echo site_url('document_share/login/' . $hash); ?>" method="POST" class="space-y-6 relative z-10">
            <input type="hidden" name="<?php echo $this->security->get_csrf_token_name(); ?>" value="<?php echo $this->security->get_csrf_hash(); ?>">
            <div>
                <label for="email" class="block text-sm font-semibold text-neutral-300 mb-2">Email Address</label>
                <input type="email" name="email" id="email" required
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-neutral-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    placeholder="Enter your registered email">
            </div>

            <div>
                <label for="phone" class="block text-sm font-semibold text-neutral-300 mb-2">Password (Phone Number)</label>
                <input type="password" name="phone" id="phone" required
                    class="w-full bg-white/5 border border-white/10 rounded-xl px-4 py-3 text-white placeholder-neutral-500 focus:outline-none focus:ring-2 focus:ring-blue-500 focus:border-transparent transition-all"
                    placeholder="Enter your phone number">
            </div>

            <button type="submit"
                class="w-full bg-gradient-to-r from-blue-600 to-indigo-600 hover:from-blue-500 hover:to-indigo-500 text-white font-bold py-3.5 px-4 rounded-xl shadow-lg shadow-blue-500/25 hover:shadow-blue-500/40 transition-all duration-300 transform active:scale-[0.98]">
                Sign In to Portal
            </button>
        </form>
        
        <div class="mt-8 pt-6 border-t border-white/5 text-center relative z-10">
            <p class="text-xs text-neutral-500">&copy; <?php echo date('Y'); ?> Secure Document Sharing Portal. All rights reserved.</p>
        </div>
    </div>
</body>
</html>
