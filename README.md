# Twinger core wordpress (Theme main)

## Prerequisite

- VS Code's extensions:
  - EditorConfig
  - TODO Highlight
  - Code Spell Checker

- Yarn: <https://yarnpkg.com/>

- plugin:
  - advanced-custom-fields-pro
  - disable-json-api
  - ithemes-security-pro
  - w3-total-cache
  - wp-media-folder
  - wp-mail-smtp

## Note (./wp-content/themes/main)

- Use `yarn` instead of `npm`.
- Don't forget to commit `yarn.lock` when you are adding new packages.

## How to Start

- Install grunt

```sh
npm install -g grunt
```

- Install app dependencies:

```sh
yarn install
```

- Combine sass:

```sh
grunt watch
```

## Project Structure

- `wp-admin`: Thư mục chứa các file liên quan đến toàn bộ trang quản lý của Wordpress.
- `wp-content`: Thư mục làm việc chính
  - `languages`: Thư mục chứa các file liên quan đến ngôn ngữ của Wordpress.
  - `plugins`: Thư mục chứa các plugin đã được cài đặt.
  - `themes`: Thư mục chứa tất cả các themes đã được thêm.
    - `main`: Thư mục theme làm viêc chính đã được custom.
      - `assets`: Lưu trữ css, js, forn, image,...
      - `inc`: Lưu trữ các cấu hình và thay đổi trang admin.
      - `pages-template`: Lưu trữ source các page trong website.
      - `template-parts`: Component.
      - `package.json`: dependencies.
      - `Gruntfile.js`: File cấu hình combine sass and js.
      - `style.css`: File style mặc định
      - `style.min.css`: File style min đã được combine từ sass
      - ...
  - `upload`:  Thư mục chưa tất cả các file được upload từ trang quản lý Wordpress.
- `wp-includes`: Thư mục chứa các chức năng của Wordpress.
- `.htaccess`: Cấu hình đường dẫn tĩnh.
- `index.php`: file default.
- `wp-activate.php`: Xác nhận qua email khi user đăng ký hoặc đăng nhập, hoặc quên mật khẩu.
- `wp-blog-header.php`: Load thư viện và theme của Wordpress.
- `wp-comments-post.php`:Xử lý featured comment.
- `wp-config.php`: Thiết lập cài đặt cho Wordpress.
- `wp-config-sample.php`: File này là file mẫu của wp-config.php.
- `wp-cron.php`: Điều khiển những công việc mang tính lịch trình, thay thế cho crontab setting trong server.
- `wp-links-opml.php`: Khi sử dụng export trong trang quản lý, các liên kết sẽ không được export, do đó file này sẽ dùng cho việc xử lý export các liên kết đó, các liên kết này sẽ được thể hiện dưới dạng cấu trúc XML.
- `wp-load.php`: Liên kết, điều hướng để load các nội dung Wordpress khi được yêu cầu.
- `wp-login.php`: Hiển thị và xử lý nội dung đăng nhập.
- `wp-mail.php`: Xử lý nhận tin nhắn từ hộp thư của người dùng để hiển thị dưới dạng post.
- `wp-settings.php`: Cài đặt và cấu hình chung cho Wordpress.
- `wp-signup.php`: Hiển thị và xử lý nội dung đăng ký.
- `wp-trackback.php`: Xử lý trackback (trackback cho phép một trang web thông báo cho người khác về một bản cập nhật nội dung mới) và pingpack (yêu cầu thông báo khi ai đó liên kết với một trong các tài liệu của trang web, giúp theo dõi xem ai đang liên kết hoặc tham khảo các bài viết).
- `xmlrpc.php`: File này giúp Wordpress giao tiếp với các hệ thống/thiết bị bên ngoài, ví dụ như dùng điện thoại để chỉnh sửa, theo dõi, ...

## Secure with ithemes-security-pro

- 1.Cài đặt plugin ithemes-security-pro
- 2.Truy cập tính năng Settings Import and Export có sẵn: import "itsec_options.zip" trong /setting-itheme
- 3.Setup mail Notification tại "Notification Center" trong tính năng của plugin

## Secure with disable-json-api -> Disable REST API

- Diabale tất cả các api với các role không phải là admin, editor và author
- Role editor và author: chỉ mở các api :
  - /yoast/v1
  - /yoast/v1/myyoast
  - /ithemes-security/v1
- Role admin: full API

## Speed ​​optimization with w3-total-cache

- 1. Cài đặt plugin w3-total-cache
- 2. Test toàn bộ tính năng cache của website tại Setup Guide -> kiểm tra tính khả dụng, active lazy load
- 3. Enable "Page Cache" và "Browser Cache". Nếu Enable tính năng minify thì cần chú ý kiểm tra lại web xem có lỗi ui ko.
  - Enable tính năng Opcode cache, Database Cache, Object Cache nếu cần hoặc khi server được hỗ trợ
- 4. Truy cập menu extensions active yoast SEO (plugin yoast SEO phải được cài đặt trước), Fragment Cache, AMP (không thay đổi cấu hình mặc định), Image Service (Không thay đổi cấu hình mặc đinh, Convert all images in the media library -> webp)

## Ngoài ra có thể thay thế w3-totao-cache bằng LiteSpeed Cache tùy từng hosting

## Chỉ cài thêm plugin khác khi thực sự cần thiết, không cài các plugin ko rõ nguồn gốc hoặc crack

## Style chú ý sử dụng các mixin responsive đã được quy định trong theme
