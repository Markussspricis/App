<template>
  <div class="account-background">
    <div class="account-container">
      <div class="heading_create">
        <h1>Reset password</h1>
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
                id="newPassword"
                style="padding-left: 10px;" 
                v-model="newPassword"
                autocomplete="off"
                @input="updateLabel('newPassword')"
                @focus="moveLabelUp('newPassword')"
                @blur="resetLabelPosition('newPassword')"
              />
              <label for="newPassword" class="input-label" :class="{ active: isLabelActive['newPassword'], committed: isInputCommitted['newPassword'] }">New password</label>
            </div>
            <span v-if="newPasswordError" class="error-message">Password must be at least 8 characters long.</span>
          </div>

          <div class="confirm_password">
            <div class="input-container">
              <input 
                type="password"
                maxlength="50"
                class="account-input" 
                id="confirmPassword"
                style="padding-left: 10px;" 
                v-model="confirmPassword"
                autocomplete="off"
                @input="updateLabel('confirmPassword')"
                @focus="moveLabelUp('confirmPassword')"
                @blur="resetLabelPosition('confirmPassword')"
              />
              <label for="confirmPassword" class="input-label" :class="{ active: isLabelActive['confirmPassword'], committed: isInputCommitted['confirmPassword'] }">Confirm password</label>
            </div>
            <span v-if="passwordMismatch" class="error-message">Passwords do not match.</span>
          </div>

          <div class="submit-form">
            <button type="submit" class="next" @click="validateEmail(); resetPassword()" :disabled="isButtonDisabled">Reset password</button>
          </div>

        </form>
      </div>
    </div>
  </div>
</template>

<script>
  import axios from 'axios';
  import { useRouter } from 'vue-router';
  import { ref } from 'vue';

  export default {
    name: 'UpdatePassword',
    data() {
      return {
        isLabelActive: {
          email: false,
          newPassword: false,
          confirmPassword: false,
        },
        isInputCommitted: {
          email: false,
          newPassword: false,
          confirmPassword: false,
        },
      };
    },
    computed: {
      isButtonDisabled() {
        return !(this.email.length > 0 && this.newPassword.length > 0 && this.confirmPassword.length > 0);
      },
    },
    setup () {
      const router = useRouter();
      const emailError = ref(null);
      const email = ref('');
      const newPassword = ref('');
      const confirmPassword = ref('');
      const isEmailValid = ref(true);
      const newPasswordError = ref(false);
      const passwordMismatch = ref(false);

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

      const resetPassword = async () => {
        console.log('Resetting password...');
        if (newPassword.value.length >= 8) {
          newPasswordError.value = false;

          if (newPassword.value === confirmPassword.value) {
            passwordMismatch.value = false;

            try {
              const response = await axios.post('/api/reset-password', {
                email: email.value,
                newPassword: newPassword.value,
              });

              console.log('Response:', response.data);

              const data = response.data;

              if (data.success || response.data.message === 'Password reset successful') {
                console.log('Password reset successful');
                router.push('/login');
              } else {
                console.log('Password reset failed');
              }
            } catch (error) {
              console.error(error);
            }
          } else {
            passwordMismatch.value = true;
            setTimeout(() => {
              passwordMismatch.value = false;
            }, 3000);
          }
        } else {
          newPasswordError.value = true;
          setTimeout(() => {
            newPasswordError.value = false;
          }, 3000);
        }
      };

      return {
        email,
        newPassword,
        confirmPassword,
        isEmailValid,
        validateEmail,
        resetPassword,
        emailError,
        newPasswordError,
        passwordMismatch,
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
    },
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
    background:rgba(29, 161, 242, 0.1);
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
      height: 400px;
      background-color: white;
      border-radius: 20px;
      border: 1px solid #1da1f2;
      font-family: Arial, Helvetica, sans-serif;
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
          .confirm_password {
            position: relative;
            top: 140px;
            left: 40px;
            width: 420px;
          }
          .submit-form{
            .next {
              background-color: #1da1f2;
              border: none;
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
              top: 340px;
              cursor: pointer;
              color: white;
            }
            .next:disabled{
              color: gray;
              background-color: #0e537e;
            }
            .next:not(:disabled):hover {
              background-color: #2394db;
            }
          }
        }
      }
    }
  }
  @media (max-width: 1024px) {
    .account-background{
      .account-container {
        width: 450px;
        height: 330px;
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
              top: 90px;
              left: 30px;
              width: 390px;
            }
            .password {
              top: 105px;
              left: 30px;
              width: 390px;
            }
            .confirm_password {
              top: 120px;
              left: 30px;
              width: 390px;
            }
            .submit-form{
              .next {
                font-size: 12px;
                width: 390px;
                height: 35px;
                left: 30px;
                top: 280px;
              }
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
    }
  }
  ///////////////////////////////////////////////////////////////////////////////////////////////////////
  @media (max-width: 650px) {
    .account-background{
      .account-container {
        width: 550px;
        height: 400px;
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
              top: 90px;
              left: 40px;
              width: 470px;
            }
            .password {
              top: 105px;
              left: 40px;
              width: 470px;
            }
            .confirm_password {
              top: 120px;
              left: 40px;
              width: 470px;
            }
            .submit-form{
              .next {
                font-size: 14px;
                width: 470px;
                height: 50px;
                left: 40px;
                top: 320px;
              }
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
    }
  }
  ///////////////////////////////////////////////////////////////////////////////////////////////////////
  @media (max-width: 600px) {
    .account-background{
      .account-container {
        width: 500px;
        height: 350px;
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
              top: 90px;
              left: 30px;
              width: 440px;
            }
            .password {
              top: 105px;
              left: 30px;
              width: 440px;
            }
            .confirm_password {
              top: 120px;
              left: 30px;
              width: 440px;
            }
            .submit-form{
              .next {
                font-size: 13px;
                width: 440px;
                height: 40px;
                left: 30px;
                top: 290px;
              }
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
    }
  }
  ///////////////////////////////////////////////////////////////////////////////////////////////////////
  @media (max-width: 550px) {
    .account-background{
      .account-container {
        width: 450px;
        height: 320px;
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
              top: 80px;
              width: 390px;
            }
            .password {
              top: 95px;
              width: 390px;
            }
            .confirm_password {
              top: 110px;
              width: 390px;
            }
            .submit-form{
              .next {
                font-size: 12px;
                width: 390px;
                height: 35px;
                top: 270px;
              }
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
            .confirm_password {
              width: 340px;
            }
            .submit-form{
              .next {
                width: 340px;
              }
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
        height: 280px;
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
            .confirm_password {
              top: 100px;
              left: 20px;
              width: 310px;
            }
            .submit-form{
              .next {
                font-size: 11px;
                width: 310px;
                height: 30px;
                left: 20px;
                top: 230px;
              }
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
    }
  }
  ///////////////////////////////////////////////////////////////////////////////////////////////////////
  @media (max-width: 400px) {
    .account-background{
      .account-container {
        width: 300px;
        height: 250px;
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
            .confirm_password {
              top: 85px;
              left: 15px;
              width: 270px;
            }
            .submit-form{
              .next {
                font-size: 10px;
                width: 270px;
                height: 30px;
                left: 15px;
                top: 200px;
              }
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
            .confirm_password {
              width: 240px;
            }
            .submit-form{
              .next {
                width: 240px;
              }
            }
          }
        }
      }
    }
  }
</style>