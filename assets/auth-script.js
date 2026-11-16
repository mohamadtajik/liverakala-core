 document.addEventListener("DOMContentLoaded", function() {
            const body = document.body;

            // ۱. پاکسازی اجباری و غیرفعال‌سازی تکمیل خودکار در زمان لود صفحه
            const allInputs = document.querySelectorAll("input.input-text");
            allInputs.forEach(inp => {
                inp.setAttribute("autocomplete", "new-password");
                inp.value = "";
            });

            // ۲. تبدیل کلمه گذرواژه به رمز عبور در لیبل‌ها
            document.querySelectorAll("label").forEach(function(label) {
                if (label.innerHTML.includes("گذرواژه")) {
                    label.innerHTML = label.innerHTML.replace(/گذرواژه/g, "رمز عبور");
                }
            });

            // ۳. تزریق باکس ارور و دکمه‌های سوئیچ به فرم‌ها
            const loginForm = document.querySelector("form.login");
            const registerForm = document.querySelector("form.register");

            if (loginForm) {
                if (!loginForm.querySelector(".lk-ajax-error-box")) {
                    const errBox = document.createElement("div");
                    errBox.className = "lk-ajax-error-box";
                    loginForm.insertBefore(errBox, loginForm.firstChild);
                }
                if (!loginForm.querySelector(".lk-switch-to-reg")) {
                    const switchDiv = document.createElement("div");
                    switchDiv.className = "lk-auth-switch-box lk-switch-to-reg";
                    switchDiv.innerHTML = 'حساب کاربری نداری؟ <button type="button" class="lk-auth-switch-btn" data-target="register">ثبت‌نام کنید</button>';
                    loginForm.appendChild(switchDiv);
                }
            }

            if (registerForm) {
                if (!registerForm.querySelector(".lk-ajax-error-box")) {
                    const errBox2 = document.createElement("div");
                    errBox2.className = "lk-ajax-error-box";
                    registerForm.insertBefore(errBox2, registerForm.firstChild);
                }
                if (!registerForm.querySelector(".lk-switch-to-log")) {
                    const switchDiv2 = document.createElement("div");
                    switchDiv2.className = "lk-auth-switch-box lk-switch-to-log";
                    switchDiv2.innerHTML = 'قبلاً ثبت‌نام کرده‌اید؟ <button type="button" class="lk-auth-switch-btn" data-target="login">وارد شوید</button>';
                    registerForm.appendChild(switchDiv2);
                }
            }

            // ۴. مدیریت تغییر وضعیت بین فرم‌ها بدون رفرش
            document.addEventListener("click", function(e) {
                if (e.target && e.target.classList.contains("lk-auth-switch-btn")) {
                    e.preventDefault();
                    const targetMode = e.target.getAttribute("data-target");

                    document.querySelectorAll("input.input-text").forEach(inp => inp.value = "");
                    document.querySelectorAll(".lk-ajax-error-box").forEach(box => {
                        box.style.display = "none";
                        box.innerHTML = "";
                    });

                    if (targetMode === "register") {
                        body.classList.add("auth-mode-register");
                    } else {
                        body.classList.remove("auth-mode-register");
                    }
                }
            });

            // ۵. ارسال فرم ورود به صورت هوشمند (خطای آنی برای فیلد خالی، ایجکس و لودینگ برای سرور)
            if (loginForm) {
                loginForm.addEventListener("submit", function(e) {
                    e.preventDefault();
                    const userField = loginForm.querySelector("#username").value.trim();
                    const passField = loginForm.querySelector("#password").value.trim();
                    const errBox = loginForm.querySelector(".lk-ajax-error-box");
                    const submitBtn = loginForm.querySelector("button[type='submit']");
                    
                    errBox.style.display = "none";
                    errBox.innerHTML = "";

                    if (!userField || !passField) {
                        errBox.innerHTML = "لطفاً نام کاربری و رمز عبور خود را وارد کنید.";
                        errBox.style.display = "block";
                        return;
                    }

                    const originalBtnText = submitBtn.innerHTML;
                    submitBtn.classList.add("loading");
                    submitBtn.innerHTML = 'در حال ورود... <span class="lk-spinner"></span>';

                    const formData = new FormData(loginForm);
                    formData.append('login', 'حساب کاربری');

                    fetch(window.location.href, {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const errorElement = doc.querySelector('.woocommerce-error');

                        if (errorElement) {
                            submitBtn.classList.remove("loading");
                            submitBtn.innerHTML = originalBtnText;

                            errBox.innerHTML = errorElement.innerHTML;
                            errBox.style.display = "block";
                        } else {
                            submitBtn.innerHTML = 'ورود موفق! در حال انتقال...';
                            window.location.reload();
                        }
                    })
                    .catch(err => {
                        submitBtn.classList.remove("loading");
                        submitBtn.innerHTML = originalBtnText;

                        errBox.innerHTML = "خطایی در ارتباط با سرور رخ داد. لطفاً دوباره تلاش کنید.";
                        errBox.style.display = "block";
                    });
                });
            }

            // ۶. ارسال فرم ثبت‌نام به صورت هوشمند (خطای آنی برای فیلد خالی، ایجکس و لودینگ برای سرور)
            if (registerForm) {
                registerForm.addEventListener("submit", function(e) {
                    e.preventDefault();
                    const emailField = registerForm.querySelector("#reg_email").value.trim();
                    const passField = registerForm.querySelector("#reg_password").value.trim();
                    const errBox2 = registerForm.querySelector(".lk-ajax-error-box");
                    const submitBtn2 = registerForm.querySelector("button[type='submit']");

                    errBox2.style.display = "none";
                    errBox2.innerHTML = "";

                    if (!emailField || !passField) {
                        errBox2.innerHTML = "لطفاً ایمیل و رمز عبور خود را وارد کنید.";
                        errBox2.style.display = "block";
                        return;
                    }

                    const originalBtnText2 = submitBtn2.innerHTML;
                    submitBtn2.classList.add("loading");
                    submitBtn2.innerHTML = 'در حال ثبت‌نام... <span class="lk-spinner"></span>';

                    const formData2 = new FormData(registerForm);
                    formData2.append('register', 'ثبت‌نام');

                    fetch(window.location.href, {
                        method: 'POST',
                        body: formData2
                    })
                    .then(response => response.text())
                    .then(html => {
                        const parser = new DOMParser();
                        const doc = parser.parseFromString(html, 'text/html');
                        const errorElement = doc.querySelector('.woocommerce-error');

                        if (errorElement) {
                            submitBtn2.classList.remove("loading");
                            submitBtn2.innerHTML = originalBtnText2;

                            errBox2.innerHTML = errorElement.innerHTML;
                            errBox2.style.display = "block";
                        } else {
                            submitBtn2.innerHTML = 'ثبت‌نام موفق! در حال انتقال...';
                            window.location.reload();
                        }
                    })
                    .catch(err => {
                        submitBtn2.classList.remove("loading");
                        submitBtn2.innerHTML = originalBtnText2;

                        errBox2.innerHTML = "خطایی در ارتباط با سرور رخ داد. لطفاً دوباره تلاش کنید.";
                        errBox2.style.display = "block";
                    });
                });
            }
        });