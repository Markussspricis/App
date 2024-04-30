<template>
  <div class="account-background">
    <div class="account-container">
      <div class="heading_create">
        <h1>Login</h1>
      </div>
      <div class="div-form">
        <form class="form">

          <div class="email">
            <div class="input-container">
              <input 
                type="text"
                maxlength="50"
                class="account-input"  
                id="email"
                style="padding-left: 10px;" 
                v-model="email" 
                autocomplete="off"
                @input="updateLabel('email')"
                @focus="moveLabelUp('email')"
                @blur="resetLabelPosition('email')"
                :class="{ 'invalid-email': !isEmailValid.value }"
              />
              <label for="email" class="input-label" :class="{ active: isLabelActive['email'], committed: isInputCommitted['email'] }">Email</label>
            </div>
            <span v-if ="emailError" class="error-message">{{ emailError }}</span>
          </div>

          <div class="password">
            <div class="input-container">
              <input 
                type="password"
                maxlength="50"
                class="account-input" 
                id="password"
                style="padding-left: 10px;" 
                v-model="password"
                autocomplete="off"
                @input="updateLabel('password')"
                @focus="moveLabelUp('password')"
                @blur="resetLabelPosition('password')"
              />
              <label for="password" class="input-label" :class="{ active: isLabelActive['password'], committed: isInputCommitted['password'] }">Password</label>
            </div>
            <span v-if ="errorLogin" class="error-message">{{ errorLogin }}</span>
          </div>

          <router-link to="/password-update" class="forgot-password-link">Forgot your password</router-link>
          <div class="submit-form">
            <button type="submit" class="next" @click="validateEmail(); login($event)" :disabled="isButtonDisabled">Login</button>
          </div>
          <router-link to="/register" class="login-link">Don't have an account? Register</router-link>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
  import axios from 'axios';
  import { useStore } from 'vuex';
  import { useRouter } from 'vue-router';
  import { ref } from 'vue';

  export default {
    name: 'Login',

    data() {
      return {
        email: '',
        password: '',
        errorLogin:null,
        emailError: null,
        isLabelActive: {
          email: false,
          password: false,
        },
        isInputCommitted: {
          email: false,
          password: false,
        },
      };
    },

    computed: {
      isButtonDisabled() {
        return !(this.email.length > 0 && this.password.length > 0)
      },
    },

    setup () {
      const router = useRouter();
      const store = useStore();
      const errorLogin = ref(null);
      const emailError = ref(null);
      const password = ref('');
      const email = ref('');
      const isEmailValid = ref(true);

      if (store.state.isLoggedIn) {
        router.push('/home');
      }

      const validateEmail = async () => {
        email.value = email.value.toLowerCase();
        const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
        isEmailValid.value = emailRegex.test(email.value);

        if (isEmailValid.value) {
          try {
            const response = await axios.post('/api/check-email', {
              Email: email.value,
            });

            const data = response.data;

            if (data.success) {
              emailError.value = null;

            } else {
              emailError.value = 'Email is not registered.';
              setTimeout(() => { emailError.value = null; }, 3000);
            }
          } catch (error) {
            emailError.value = null;
          }
        } else{
          emailError.value = 'Please enter a valid email.';
          setTimeout(() => { emailError.value = null; }, 3000);
        }
      };

      const login = async (event) => {
        event.preventDefault();
        console.log('Login button clicked');
        if (password.value.length > 0) {
          try {
            const loginSuccess = await store.dispatch('login', {
              Email: email.value,
              Password: password.value,
            });

            console.log('Login success:', loginSuccess);

            if (loginSuccess) {
              router.push('/home');
            }
          } catch (error) {
            errorLogin.value = 'Invalid password.';
            setTimeout(() => { errorLogin.value = null; }, 3000);
            console.error(error);
          }
        }
      };

      return {
        email,
        password,
        isEmailValid,
        validateEmail,
        login,
        errorLogin,
        emailError,
      }
    },

    methods: {
      updateLabel(fieldName) {
        this.isLabelActive[fieldName] = this[fieldName].length > 0;
        this.isInputCommitted[fieldName] = false;
      },

      moveLabelUp(fieldName) {
        this.isLabelActive[fieldName] = true;
        this.isInputCommitted[fieldName] = false;
      },
      
      resetLabelPosition(fieldName) {
        if (this[fieldName].length === 0) {
          this.isLabelActive[fieldName] = false;
        }
        if (this[fieldName].length > 0) {
          this.isInputCommitted[fieldName] = true;
        }
      },
    }
  }
</script>

<style lang="scss" scoped>
  .error-message {
    color: red;
    font-size: 12px;
    position: absolute;
    left: 0;
    top: 105%;
  }
  .input-container {
    position: relative;
    display: inline-block;
  }
  .input-label {
    position: absolute;
    left: 10px;
    transition: 0.3s ease all;
    font-size: 15px;
    color: black;
    pointer-events: none;
    top: 50%;
    transform: translateY(-50%);
  }
  .input-label.active {
    transform: translateY(-100%);
    font-size: 10px;
    color: #1da1f2;
  }
  .input-label.committed {
    color: black;
  }
  input[type="text"], input[type="password"] {
    box-sizing: border-box;
    height: 50px;
    width: 420px;
    border-radius: 10px;
    background-color: white;
    color: black;
  }
  .account-input {
    border: 1px solid black;
    outline: none;
    padding-top: 20px;
  }
  .account-input:focus {
    border: 1px solid #1da1f2;
  }
  .account-background{
    background:rgba(3, 19, 28, 0.1);
    z-index: 99;
    position: fixed;
    top: 0;
    left: 0;
    right:0;
    bottom:0;
    .account-container {
      z-index: 100;
      position: absolute;
      top: 50%;
      left: 50%;
      transform: translate(-50%, -50%);
      width: 500px;
      height: 370px;
      background-color: white;
      border-radius: 20px;
      border: 1px solid black;
      color: black;
      .heading_create {
        position: absolute;
        top: 20px;
        left: 40px;
      }
      .div-form{
        .form{
          .email {
            position: relative;
            top: 100px;
            left: 40px;
            width: 420px;
          }
          .password {
            position: relative;
            top: 120px;
            left: 40px;
            width: 420px;
          }
          .forgot-password-link {
            position: absolute;
            top: 235px;
            left: 40px;
            font-size: 14px;
          }
          .submit-form{
            .next {
              background-color: white;
              border: 2px solid black;
              border-radius: 50px;
              font-weight: bold;
              padding: 10px 20px;
              text-align: center;
              text-decoration: none;
              font-size: 14px;
              position: absolute;
              width: 420px;
              height: 40px;
              left: 40px;
              top: 290px;
              cursor: pointer;
              color: black;
              transition: all 0.3s;
            }
            .next:disabled{
              color: gray;
              background-color: #a6a6a6;
              border: 2px solid gray;
            }
            .next:not(:disabled):hover {
              background-color: #f2f2f2;
            }
          }
          .login-link {
            position: absolute;
            top: 335px;
            left: 150px;
            font-size: 14px;
          }
        }
      }
    }
  }
  @media (max-width: 1024px) {
    .account-background{
      .account-container {
        width: 450px;
        height: 300px;
        .heading_create {
          top: 22px;
          left: 30px;
          font-size: 12px;
        }
        .div-form{
          .form{
            input[type="text"], input[type="password"] {
              height: 40px;
              width: 390px;
            }
            .email {
              top: 85px;
              left: 30px;
              width: 390px;
            }
            .password {
              top: 100px;
              left: 30px;
              width: 390px;
            }
            .forgot-password-link {
              top: 195px;
              left: 30px;
              font-size: 12px;
            }
            .submit-form{
              .next {
                font-size: 12px;
                width: 390px;
                height: 35px;
                left: 30px;
                top: 230px;
              }
            }
            .login-link {
              top: 275px;
              left: 140px;
              font-size: 12px;
            }
          }
        }
      }
      .input-label {
        font-size: 12px;
      }
      .input-label.active {
        font-size: 9px;
      }
      .account-input {
        padding-top: 18px;
        font-size: 11px;
      }
      .error-message {
        font-size: 10px;
      }
      .error-message-2 {
        font-size: 10px;
        top: 85%;
        left: 30px;
      }
    }
  }
  ///////////////////////////////////////////////////////////////////////////////////////////////////////
  @media (max-width: 650px) {
    .account-background{
      .account-container {
        width: 550px;
        height: 350px;
        .heading_create {
          top: 10px;
          left: 40px;
          font-size: 14px;
        }
        .div-form{
          .form{
            input[type="text"], input[type="password"] {
              height: 50px;
              width: 470px;
            }
            .email {
              top: 80px;
              left: 40px;
              width: 470px;
            }
            .password {
              top: 95px;
              left: 40px;
              width: 470px;
            }
            .forgot-password-link {
              top: 210px;
              left: 40px;
              font-size: 14px;
            }
            .submit-form{
              .next {
                font-size: 14px;
                width: 470px;
                height: 50px;
                left: 40px;
                top: 260px;
              }
            }
            .login-link {
              top: 320px;
              left: 175px;
              font-size: 14px;
            }
          }
        }
      }
      .input-label {
        font-size: 14px;
      }
      .input-label.active {
        font-size: 11px;
      }
      .account-input {
        font-size: 13px;
      }
      .error-message {
        top: 100%;
        font-size: 12px;
      }
      .error-message-2 {
        top: 82%;
        left: 40px;
        font-size: 12px;
      }
    }
  }
  ///////////////////////////////////////////////////////////////////////////////////////////////////////
  @media (max-width: 600px) {
    .account-background{
      .account-container {
        width: 500px;
        height: 320px;
        .heading_create {
          top: 15px;
          left: 30px;
          font-size: 13px;
        }
        .div-form{
          .form{
            input[type="text"], input[type="password"] {
              height: 45px;
              width: 440px;
            }
            .email {
              left: 30px;
              width: 440px;
            }
            .password {
              left: 30px;
              width: 440px;
            }
            .forgot-password-link {
              top: 200px;
              left: 30px;
              font-size: 13px;
            }
            .submit-form{
              .next {
                font-size: 13px;
                width: 440px;
                height: 40px;
                left: 30px;
                top: 245px;
              }
            }
            .login-link {
              top: 290px;
              left: 157px;
              font-size: 13px;
            }
          }
        }
      }
      .input-label {
        font-size: 13px;
      }
      .input-label.active {
        font-size: 10px;
      }
      .account-input {
        font-size: 12px;
      }
      .error-message {
        top: 105%;
        font-size: 11px;
      }
      .error-message-2 {
        top: 84%;
        left: 30px;
        font-size: 11px;
      }
    }
  }
  ///////////////////////////////////////////////////////////////////////////////////////////////////////
  @media (max-width: 550px) {
    .account-background{
      .account-container {
        width: 450px;
        height: 280px;
        .heading_create {
          font-size: 12px;
        }
        .div-form{
          .form{
            input[type="text"], input[type="password"] {
              height: 40px;
              width: 390px;
            }
            .email {
              width: 390px;
            }
            .password {
              width: 390px;
            }
            .forgot-password-link {
              top: 190px;
              font-size: 12px;
            }
            .submit-form{
              .next {
                font-size: 12px;
                width: 390px;
                height: 35px;
                top: 220px;
              }
            }
            .login-link {
              top: 260px;
              left: 140px;
              font-size: 12px;
            }
          }
        }
      }
      .input-label {
        font-size: 12px;
      }
      .input-label.active {
        font-size: 9px;
      }
      .account-input {
        font-size: 11px;
      }
      .error-message {
        font-size: 10px;
      }
      .error-message-2 {
        font-size: 10px;
        top: 85%;
      }
    }
  }
  ///////////////////////////////////////////////////////////////////////////////////////////////////////
  @media (max-width: 500px) {
    .account-background{
      .account-container {
        width: 400px;
        .div-form{
          .form{
            input[type="text"], input[type="password"] {
              width: 340px;
            }
            .email {
              width: 340px;
            }
            .password {
              width: 340px;
            }
            .submit-form{
              .next {
                width: 340px;
              }
            }
            .login-link {
              left: 115px;
            }
          }
        }
      }
    }
  }
  ///////////////////////////////////////////////////////////////////////////////////////////////////////
  @media (max-width: 450px) {
    .account-background{
      .account-container {
        width: 350px;
        height: 270px;
        .heading_create {
          left: 20px;
          font-size: 10px;
        }
        .div-form{
          .form{
            input[type="text"], input[type="password"] {
              height: 35px;
              width: 310px;
            }
            .email {
              top: 70px;
              left: 20px;
              width: 310px;
            }
            .password {
              top: 85px;
              left: 20px;
              width: 310px;
            }
            .forgot-password-link {
              top: 170px;
              left: 20px;
              font-size: 11px;
            }
            .submit-form{
              .next {
                font-size: 11px;
                width: 310px;
                height: 30px;
                left: 20px;
                top: 210px;
              }
            }
            .login-link {
              top: 250px;
              left: 97px;
              font-size: 11px;
            }
          }
        }
      }
      .input-label {
        font-size: 11px;
      }
      .input-label.active {
        font-size: 8px;
      }
      .account-input {
        padding-top: 16px;
        font-size: 10px;
      }
      .error-message {
        font-size: 9px;
      }
      .error-message-2 {
        font-size: 9px;
        left: 20px;
      }
    }
  }
  ///////////////////////////////////////////////////////////////////////////////////////////////////////
  @media (max-width: 400px) {
    .account-background{
      .account-container {
        width: 300px;
        height: 260px;
        .heading_create {
          top: 18px;
          left: 15px;
          font-size: 9px;
        }
        .div-form{
          .form{
            input[type="text"], input[type="password"] {
              height: 30px;
              width: 270px;
            }
            .email {
              top: 65px;
              left: 15px;
              width: 270px;
            }
            .password {
              top: 75px;
              left: 15px;
              width: 270px;
            }
            .forgot-password-link {
              top: 145px;
              left: 15px;
              font-size: 10px;
            }
            .submit-form{
              .next {
                font-size: 10px;
                width: 270px;
                height: 30px;
                left: 15px;
                top: 190px;
              }
            }
            .login-link {
              top: 230px;
              left: 79px;
              font-size: 10px;
            }
          }
        }
      }
      .input-label {
        font-size: 10px;
      }
      .input-label.active {
        font-size: 7px;
      }
      .account-input {
        padding-top: 14px;
        font-size: 9px;
      }
      .error-message {
        top: 100%;
        font-size: 8px;
      }
      .error-message-2 {
        top: 84%;
        left: 15px;
        font-size: 8px;
      }
    }
  }
  ///////////////////////////////////////////////////////////////////////////////////////////////////////
  @media (max-width: 310px) {
    .account-background{
      .account-container {
        width: 270px;
        .heading_create {
          font-size: 8px;
        }
        .div-form{
          .form{
            input[type="text"], input[type="password"] {
              width: 240px;
            }
            .email {
              width: 240px;
            }
            .password {
              width: 240px;
            }
            .submit-form{
              .next {
                width: 240px;
              }
            }
            .login-link {
              left: 63px;
            }
          }
        }
      }
    }
  }
</style>