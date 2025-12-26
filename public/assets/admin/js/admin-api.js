// Admin Panel API Helper
const AdminAPI = {
    baseUrl: '../api/admin/',
    
    // News Management
    news: {
        list: function() {
            return $.ajax({
                url: AdminAPI.baseUrl + 'news_manage.php',
                method: 'GET',
                dataType: 'json'
            });
        },
        create: function(data) {
            return $.ajax({
                url: AdminAPI.baseUrl + 'news_manage.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data),
                dataType: 'json'
            });
        },
        update: function(data) {
            return $.ajax({
                url: AdminAPI.baseUrl + 'news_manage.php',
                method: 'PUT',
                contentType: 'application/json',
                data: JSON.stringify(data),
                dataType: 'json'
            });
        },
        delete: function(id) {
            return $.ajax({
                url: AdminAPI.baseUrl + 'news_manage.php',
                method: 'DELETE',
                contentType: 'application/json',
                data: JSON.stringify({id: id}),
                dataType: 'json'
            });
        }
    },
    
    // Tasks Management
    tasks: {
        list: function(type) {
            return $.ajax({
                url: AdminAPI.baseUrl + 'tasks_manage.php?type=' + (type || 'all'),
                method: 'GET',
                dataType: 'json'
            });
        },
        updateDaily: function(data) {
            data.task_type = 'daily';
            return $.ajax({
                url: AdminAPI.baseUrl + 'tasks_manage.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data),
                dataType: 'json'
            });
        },
        createOnetime: function(data) {
            data.task_type = 'onetime';
            return $.ajax({
                url: AdminAPI.baseUrl + 'tasks_manage.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data),
                dataType: 'json'
            });
        },
        update: function(data) {
            return $.ajax({
                url: AdminAPI.baseUrl + 'tasks_manage.php',
                method: 'PUT',
                contentType: 'application/json',
                data: JSON.stringify(data),
                dataType: 'json'
            });
        },
        delete: function(id) {
            return $.ajax({
                url: AdminAPI.baseUrl + 'tasks_manage.php',
                method: 'DELETE',
                contentType: 'application/json',
                data: JSON.stringify({id: id}),
                dataType: 'json'
            });
        }
    },
    
    // Shop Management
    shop: {
        list: function() {
            return $.ajax({
                url: AdminAPI.baseUrl + 'shop_manage.php',
                method: 'GET',
                dataType: 'json'
            });
        },
        create: function(data) {
            return $.ajax({
                url: AdminAPI.baseUrl + 'shop_manage.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data),
                dataType: 'json'
            });
        },
        update: function(data) {
            return $.ajax({
                url: AdminAPI.baseUrl + 'shop_manage.php',
                method: 'PUT',
                contentType: 'application/json',
                data: JSON.stringify(data),
                dataType: 'json'
            });
        },
        delete: function(id) {
            return $.ajax({
                url: AdminAPI.baseUrl + 'shop_manage.php',
                method: 'DELETE',
                contentType: 'application/json',
                data: JSON.stringify({id: id}),
                dataType: 'json'
            });
        }
    },
    
    // Giveaway Management
    giveaway: {
        list: function() {
            return $.ajax({
                url: AdminAPI.baseUrl + 'giveaway_manage.php',
                method: 'GET',
                dataType: 'json'
            });
        },
        create: function(data) {
            return $.ajax({
                url: AdminAPI.baseUrl + 'giveaway_manage.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data),
                dataType: 'json'
            });
        },
        update: function(data) {
            return $.ajax({
                url: AdminAPI.baseUrl + 'giveaway_manage.php',
                method: 'PUT',
                contentType: 'application/json',
                data: JSON.stringify(data),
                dataType: 'json'
            });
        },
        delete: function(id) {
            return $.ajax({
                url: AdminAPI.baseUrl + 'giveaway_manage.php',
                method: 'DELETE',
                contentType: 'application/json',
                data: JSON.stringify({id: id}),
                dataType: 'json'
            });
        }
    },
    
    // Settings Management
    settings: {
        get: function() {
            return $.ajax({
                url: AdminAPI.baseUrl + 'settings_manage.php',
                method: 'GET',
                dataType: 'json'
            });
        },
        updateMining: function(data) {
            data.settings_type = 'mining';
            return $.ajax({
                url: AdminAPI.baseUrl + 'settings_manage.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data),
                dataType: 'json'
            });
        },
        updateReferral: function(data) {
            data.settings_type = 'referral';
            return $.ajax({
                url: AdminAPI.baseUrl + 'settings_manage.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data),
                dataType: 'json'
            });
        },
        updateUserCount: function(data) {
            data.settings_type = 'user_count';
            return $.ajax({
                url: AdminAPI.baseUrl + 'settings_manage.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data),
                dataType: 'json'
            });
        },
        updateMysteryBox: function(data) {
            data.settings_type = 'mystery_box';
            return $.ajax({
                url: AdminAPI.baseUrl + 'settings_manage.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data),
                dataType: 'json'
            });
        },
        updateKYC: function(data) {
            data.settings_type = 'kyc';
            return $.ajax({
                url: AdminAPI.baseUrl + 'settings_manage.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data),
                dataType: 'json'
            });
        }
    },
    
    // KYC Management
    kyc: {
        list: function() {
            return $.ajax({
                url: AdminAPI.baseUrl + 'kyc_manage.php',
                method: 'GET',
                dataType: 'json'
            });
        },
        get: function(id) {
            return $.ajax({
                url: AdminAPI.baseUrl + 'kyc_manage.php?id=' + id,
                method: 'GET',
                dataType: 'json'
            });
        },
        update: function(data) {
            return $.ajax({
                url: AdminAPI.baseUrl + 'kyc_manage.php',
                method: 'PUT',
                contentType: 'application/json',
                data: JSON.stringify(data),
                dataType: 'json'
            });
        }
    },
    
    // Users Management
    users: {
        search: function(search, page, perPage) {
            return $.ajax({
                url: AdminAPI.baseUrl + 'users_manage.php?search=' + encodeURIComponent(search || '') + '&page=' + (page || 1) + '&perPage=' + (perPage || 20),
                method: 'GET',
                dataType: 'json'
            });
        },
        giveCoins: function(data) {
            return $.ajax({
                url: AdminAPI.baseUrl + 'users_manage.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data),
                dataType: 'json'
            });
        },
        giveBooster: function(data) {
            data.action = 'give_booster';
            return $.ajax({
                url: AdminAPI.baseUrl + 'users_manage.php',
                method: 'POST',
                contentType: 'application/json',
                data: JSON.stringify(data),
                dataType: 'json'
            });
        }
    }
};





