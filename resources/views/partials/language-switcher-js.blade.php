<!-- Hidden Google Translate Element -->
<div id="google_translate_element" style="display:none;"></div>

<script type="text/javascript">
    function googleTranslateElementInit() {
        new google.translate.TranslateElement({
            pageLanguage: 'es',
            includedLanguages: 'en,es,ca,hi,pa,ur',
            autoDisplay: false
        }, 'google_translate_element');
    }
    
    function setLanguage(lang, event) {
        if(event) event.preventDefault();
        
        const domain = window.location.hostname;
        const cookieName = 'googtrans';
        const cookieValue = lang === 'es' ? '' : '/es/' + lang;
        const expires = lang === 'es' ? 'Expires=Thu, 01 Jan 1970 00:00:01 GMT' : '';
        
        // Comprehensive cookie clearing/setting
        const paths = ['/', '/index.php'];
        const domains = [domain, '.' + domain, ''];
        
        domains.forEach(d => {
            paths.forEach(p => {
                let cookieStr = `${cookieName}=${cookieValue}; path=${p};`;
                if (d) cookieStr += ` domain=${d};`;
                if (expires) cookieStr += ` ${expires};`;
                document.cookie = cookieStr;
            });
        });

        // Set local cookie as fallback
        document.cookie = `${cookieName}=${cookieValue}; path=/;`;
        
        // Preserve current path, update query param
        let url = new URL(window.location.href);
        url.searchParams.set('lang', lang);
        window.location.href = url.href;
    }
</script>
<script type="text/javascript" src="//translate.google.com/translate_a/element.js?cb=googleTranslateElementInit"></script>

<style>
    /* Prevent body shift and hide all Google Translate UI elements */
    body { top: 0 !important; }
    .skiptranslate.goog-te-banner-frame { display: none !important; }
    #goog-gt-tt { display: none !important; }
    .goog-tooltip, .goog-tooltip:hover { display: none !important; }
    .goog-text-highlight { background-color: transparent !important; border: none !important; box-shadow: none !important; }
</style>
