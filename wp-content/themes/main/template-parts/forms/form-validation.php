<div class="main">
    <form action="" method="POST" class="form wow bounceInUp" data-wow-duration="1s" data-wow-delay=".2s" id="form-1">
        <h3 class="heading">Thành viên đăng ký</h3>

        <div class="spacer"></div>

        <div class="form-group">
            <label for="fullname" class="form-label">Tên đầy đủ</label>
            <input id="fullname" name="fullname" type="text" placeholder="VD: Ba Nguyen" class="form-control">
            <span class="form-message"></span>
        </div>

        <div class="form-group">
            <label for="email" class="form-label">Email</label>
            <input id="email" name="email" type="text" placeholder="VD: email@domain.com" class="form-control">
            <span class="form-message"></span>
        </div>

        <div class="form-group">
            <label for="phone" class="form-label">Số điện thoại</label>
            <input id="phone" name="phone" type="text" placeholder="VD: 0965420xxx" class="form-control">
            <span class="form-message"></span>
        </div>

        <div class="form-group">
            <label for="password" class="form-label">Mật khẩu</label>
            <input id="password" name="password" type="password" placeholder="Nhập mật khẩu" class="form-control" autocomplete="off">
            <span class="form-message"></span>
        </div>

        <div class="form-group">
            <label for="password_confirmation" class="form-label">Nhập lại mật khẩu</label>
            <input id="password_confirmation" name="password_confirmation" placeholder="Nhập lại mật khẩu" type="password" class="form-control" autocomplete="off">
            <span class="form-message"></span>
        </div>
        <div class="form-group isRadio">
            <div class="radio">
                <input name="gender" type="radio" class="form-control" value="male" id="gender-male">
                <label for="gender-male" class="form-label">Nam</label>
            </div>
            <div class="radio">
                <input name="gender" type="radio" class="form-control" value="female" id="gender-female">
                <label for="gender-female" class="form-label">Nữ</label>
            </div>
            <span class="form-message"></span>
        </div>

        <div class="form-group isRadio">
            <div class="radio">
                <input name="gender-c" type="checkbox" class="form-control" value="male" id="gender-male-1">
                <label for="gender-male-1" class="form-label">Nam</label>
            </div>
            <div class="radio">
                <input name="gender-c" type="checkbox" class="form-control" value="female" id="gender-female-1">
                <label for="gender-female-1" class="form-label">Nữ</label>
            </div>
            <span class="form-message"></span>
        </div>

        <button class="form-submit">Đăng ký</button>
    </form>
</div>
<script>
    jQuery(document).ready(function($) {
        Validator({
            form: '#form-1',
            formGroupSelector: '.form-group',
            errorSelector: '.form-message',
            classError: 'invalid',
            rules: [
                Validator.isRequired('#fullname'),
                Validator.isMinLength('#fullname', 3),
                Validator.isText('#fullname'),

                Validator.isRequired('#email'),
                Validator.isEmail('#email'),

                Validator.isRequired('#phone'),
                // Validator.isNumber('#phone'),
                Validator.isPhone('#phone'),

                Validator.isRequired('#password'),
                Validator.isMinLength('#password', 3), // min: 6 (default)

                Validator.isRequired('#password_confirmation'),
                Validator.isConfirmed('#password_confirmation', () => document.querySelector('#form-1 #password').value, 'The password is not correct.'),

                Validator.isRequired('input[name="gender"]'),

                Validator.isRequired('input[name="gender-c"]')
            ],
            //nếu không truyền vào callback submit sẽ nhận trường hợp submit mặc định của form
            onSubmit: (data) => {
                const token = document.querySelector('#contact_nonce').value
                jQuery.ajax({
                    type: 'POST',
                    url: obj.AJAX_URL,
                    data: {
                        action: 'formCommonAjax',
                        data: data,
                        nonce: token,
                        postTitle: 'fullname',
                        postType: 'register'
                    },
                    // dataType: 'JSON',
                    beforeSend: () => {},
                    complete: () => {},
                    success: (res) => {
                        console.log(res)
                    },
                    error: (jqXHR, textStatus, errorThrown) => {
                        console.log('The following error occured: ' + jqXHR, textStatus, errorThrown);
                    }
                });
            }
        });
    });
</script>