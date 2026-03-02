function getToken() {

    var token = $('meta[name="x-refresh-token"]').attr('content');

    if (!token) {
        // Auto submit logout form
        $('#autoLogoutForm').submit();
        return false;
    }

    return token;
}