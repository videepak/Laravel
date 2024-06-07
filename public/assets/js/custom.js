$('#add_subscription').validate({
     rules: {
        package_name: {required: true, noSpace: true},
        admin: {required: true},
        field_controller: {required: true},
        code_package: {required: true}
        
    },
    messages: {
        package_name: {required: 'Enter package name.'},
        admin: {required: 'Enter total admin.'},
        field_controller: {required: 'Enter field contoller.'},
        code_package: {required: 'Enter code package.'}
    }
});