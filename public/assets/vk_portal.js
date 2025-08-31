(function() {
    if (!('VKIDSDK' in window)) return;
    const VKID = window.VKIDSDK;

    VKID.Config.init({
        app: 54095571,
        redirectUrl: 'http://localhost/auth-system/public/oauth_vk_callback.php',
        responseMode: VKID.ConfigResponseMode.Callback,
        source: VKID.ConfigSource.LOWCODE,
        scope: ''
    });

    window.renderVkButton = function(id, label) {
        const box = document.getElementById(id);
        if (!box) return;
        const oneTap = new VKID.OneTap();
        oneTap
            .render({
                container: box,
                fastAuthEnabled: false,
                showAlternativeLogin: true
            })
            .on(VKID.WidgetEvents.ERROR, console.error)
            .on(VKID.OneTapInternalEvents.LOGIN_SUCCESS, function(payload) {
                const code = payload.code;
                const deviceId = payload.device_id;
                VKID.Auth.exchangeCode(code, deviceId)
                    .then(function(data) {
                        const vkUserId = data.user_id || (data.user && data.user.id);
                        fetch('/auth-system/public/oauth_vk_callback.php', {
                            method: 'POST',
                            headers: { 'Content-Type': 'application/json' },
                            body: JSON.stringify({ vk_user_id: vkUserId })
                        })
                        .then(function() { location.href = '/auth-system/public/protected.php'; })
                        .catch(console.error);
                    })
                    .catch(console.error);
            });

        setTimeout(function() {
            var btn = box.querySelector('button');
            if (btn) btn.textContent = label;
        }, 0);
    };
})();