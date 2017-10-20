YUI().use("json", "event", "io-base", "node-base", function(Y) { 

display = function(e) {alert('here');
     var val = document.getElementById('menucatlist');
     catvalue= val.value;
     if (catvalue) {
        Y.io("/citest/blocks/courseprerequisitelist/prerequisite.php", {
             method: "POST",
             data: "id="+catvalue,
                on: {
                    success: function (id, result) {
                         //To do
                         obj = JSON.parse(result.response);
                         document.getElementById("tableid").innerHTML = obj;
                         console.log(result);
                    }
                }
        });
     }
}

Y.on('click', display, "#id_display");
});