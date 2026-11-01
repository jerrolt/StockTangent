In your EXISTING create_users_table migration, add $table->softDeletes(); immediately after 
$table->timestamps(); in the users Schema::create block. Do not replace the default Breeze 
migration; retain password_reset_tokens and sessions tables. If users already exist in your 
database, instead create a NEW migration: 
    php artisan make:migration add_deleted_at_to_users_table --table=users, 
    with 
    up(): Schema::table('users', fn (Blueprint $table) => $table->softDeletes()); and 
    down(): Schema::table('users', fn (Blueprint $table) => $table->dropSoftDeletes());
