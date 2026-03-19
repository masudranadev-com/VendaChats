const metaColorAPI = "http://localhost:8081/api/test/meta";

const metaColorFunction = () => {
    $.ajax({
        url: metaColorAPI,
        method: "GET",
        success: function (data) {
            console.log(data);
        },
        error: function (xhr, status, error) {
            console.error(error);
        }
    });
}

// INIT 
metaColorFunction();