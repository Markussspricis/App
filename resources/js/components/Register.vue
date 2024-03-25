<template>
  <div class="account-background">
    <div class="account-container">
      <div class="heading_create">
        <h1>Create your account</h1>
      </div>
      <div class="div-form">
        <form class="form">

          <div class="name">
            <div class="input-container">
              <input 
                type="text"
                maxlength="50"
                class="account-input" 
                id="name" 
                style="padding-left: 10px;"
                v-model="name"
                autocomplete="off"
                @input="updateLabel('name')"
                @focus="moveLabelUp('name')"
                @blur="resetLabelPosition('name')"
              />
              <label for="name" class="input-label" :class="{ active: isLabelActive['name'], committed: isInputCommitted['name'] }">Name</label>
            </div>
            <span v-if="nameHasSpaces" class="error-message">Name should not contain spaces.</span>
          </div>

          <div class="username">
            <div class="input-container">
              <input 
                type="text"
                maxlength="50"
                class="account-input" 
                id="username"
                style="padding-left: 10px;" 
                v-model="username"
                autocomplete="off"
                @input="updateLabel('username')"
                @focus="moveLabelUp('username')"
                @blur="resetLabelPosition('username')"
              />
              <label for="username" class="input-label" :class="{ active: isLabelActive['username'], committed: isInputCommitted['username'] }">Username</label>
            </div>
            <span v-if="usernameHasSpaces" class="error-message">Username should not contain spaces.</span>
            <span v-if="usernameError" class="error-message">{{ usernameError }}</span>
          </div>

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
              />
              <label for="email" class="input-label" :class="{ active: isLabelActive['email'], committed: isInputCommitted['email'] }">Email</label>
            </div>
            <span v-if="invalidEmail" class="error-message">Please enter a valid email address.</span>
            <span v-if="emailError" class="error-message">{{ emailError }}</span>
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
            <span v-if="passwordWarningVisible" class="error-message">Password must be at least 8 characters long.</span>
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
            <span v-if="passwordsDoNotMatch" class="error-message">Passwords do not match.</span>
          </div>

          <div class="birth_date">
            <h3 class="date">Date of birth</h3>
            <p class="p">This will not be shown publicly. Confirm your own age, even if this account is for a business, a pet, or something else.</p>
          </div>
          <div class="time">
            <div class="months">
              <select class="select-1" v-model="month" ref="selectMonth" id="month" @change="updateDays()">
                <option value="" disabled selected>Month</option>
                <option v-for="(month, index) in months" :key="index+1" :value="index+1">{{ month }}</option>
              </select>
            </div>
            <div class="days">
              <select class="select-2" v-model="day" ref="selectDay" id="day">
                <option value="" disabled selected>Day</option>
                <option v-for="day in days" :key="day">{{ day }}</option>
              </select>
            </div>
            <div class="years">
              <select class="select-3" v-model="year" ref="selectYear" id="year">
                <option value="" disabled selected>Year</option>
                <option v-for="year in years" :key="year">{{ year }}</option>
              </select>
            </div>
          </div>
          <div class="submit-form">
            <button type="submit" class="next" @click="validateForm" v-bind:disabled="!allFieldsFilled" id="next">Create account</button>
          </div>
          <router-link to="/login" class="login-link">Already have an account? Login</router-link>
        </form>
      </div>
    </div>
  </div>
</template>

<script>
  const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
  export default {
    name: 'Register',
    data() {
      return {
        name: '',
        username: '',
        email: '',
        password: '',
        confirmPassword: '',
        day: '',
        month: '',
        year: '',
        dob: '',
        months: [
          'January', 'February', 'March', 'April', 'May', 'June', 
          'July', 'August', 'September', 'October', 'November', 'December'
        ],
        years: Array.from({ length: 100 }, (_, index) => new Date().getFullYear() - index),
        nameHasSpaces: false,
        usernameHasSpaces: false,
        invalidEmail: false,
        passwordWarningVisible: false,
        passwordsDoNotMatch: false,
        isLabelActive: {
          name: false,
          username: false,
          email: false,
          password: false,
          confirmPassword: false,
        },
          isInputCommitted: {
          name: false,
          username: false,
          email: false,
          password: false,
          confirmPassword: false,
        },
        emailError: '',
        usernameError: '',
        error:null,
      };
    },
    computed: {
      days() {
        if (this.month === '') return [];
        const selectedMonth = parseInt(this.month);
        const daysInMonth = new Date(this.year, selectedMonth, 0).getDate();
        return Array.from({ length: daysInMonth }, (_, index) => index + 1);
      },
      allFieldsFilled() {
        return this.name && this.username && this.email && this.password && this.confirmPassword && this.month && this.day && this.year;
      },
    },
    mounted() {
      this.$refs.selectMonth.style.color = "black";
      this.$refs.selectDay.style.color = "black";
      this.$refs.selectYear.style.color = "black";
    },
    methods: {
      validateForm(e) {
        e.preventDefault()
        this.nameHasSpaces = false;
        this.usernameHasSpaces = false;
        this.invalidEmail = false;
        this.passwordsDoNotMatch = false;

        if (this.name.includes(' ')) {
          this.nameHasSpaces = true;
          setTimeout(() => { this.nameHasSpaces = false; }, 3000);
          return;
        }

        if (this.username.includes(' ')) {
          this.usernameHasSpaces = true;
          setTimeout(() => { this.usernameHasSpaces = false; }, 3000);
          return;
        }

        if (!emailRegex.test(this.email)) {
          this.invalidEmail = true;
          setTimeout(() => { this.invalidEmail = false; }, 3000);
          return;
        }

        if (this.password.length < 8) {
          this.passwordWarningVisible = true;
          setTimeout(() => {
            this.passwordWarningVisible = false;
          }, 3000);
          return;
        }

        if (this.password !== this.confirmPassword) {
          this.passwordsDoNotMatch = true;
          setTimeout(() => { this.passwordsDoNotMatch = false; }, 3000);
          return;
        }
        if (!this.nameHasSpaces && !this.usernameHasSpaces && !this.invalidEmail && !this.passwordsDoNotMatch) {
          this.register();
        }
      },
      monthNameToNumber(monthName) {
        const months = [
          "January", "February", "March", "April", "May", "June",
          "July", "August", "September", "October", "November", "December"
        ];
        const monthNumber = months.findIndex(month => month === monthName) + 1;
        return monthNumber.toString().padStart(2, '0');
      },
      async register() {
        this.emailError = '';
        this.usernameError = '';

        try {
          const registrationData = {
            Name: this.name,
            UserTag: this.username,
            Email: this.email,
            Password: this.password,
            DOB: `${this.year}-${this.monthNameToNumber(this.month)}-${this.day}`,
          };
          const response = await this.$store.dispatch('register', registrationData);

          if (response.success) {
            this.$router.push('/home');
          } else {
            if (response.message.includes('Email')) {
              this.emailError = response.message;
              setTimeout(() => {
                this.emailError = '';
              }, 3000);
            }
            if (response.message.includes('Username')) {
              this.usernameError = response.message;
              setTimeout(() => {
                this.usernameError = '';
              }, 3000);
            }
          }
        } catch (error) {
          console.error(error);
        }
      },
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
    watch: {
      month: function () {
        this.$refs.selectMonth.style.color = "black";
      },
      day: function () {
        this.$refs.selectDay.style.color = "black";
      },
      year: function () {
        this.$refs.selectYear.style.color = "black";
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
  .error-message-2 {
    color: red;
    font-size: 12px;
    position: absolute;
    left: 40px;
    top: 87%;
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
  .account-input::-webkit-input-placeholder {
    color: black;
  }
  .account-input:focus::-webkit-input-placeholder {
    color: #1da1f2;
  }
  select {
    border: 1px solid black;
    background-color: white;
    height: 50px;
    border-radius: 10px;
    outline: none;
  }
  select option:disabled { display: none; }
  select option { color: black; }
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
      height: 670px;
      background-color: white;
      border-radius: 20px;
      border: 1px solid #1da1f2;
      color: black;
      .heading_create {
        position: absolute;
        top: 20px;
        left: 40px;
      }
      .div-form{
        .form{
          .name {
            position: relative;
            top: 100px;
            left: 40px;
            width: 420px;
          }
          .username {
            position: relative;
            top: 120px;
            left: 40px;
            width: 420px;
          }
          .email {
            position: relative;
            top: 140px;
            left: 40px;
            width: 420px;
          }
          .password {
            position: relative;
            top: 160px;
            left: 40px;
            width: 420px;
          }
          .confirm_password {
            position: relative;
            top: 180px;
            left: 40px;
            width: 420px;
          }
          .birth_date {
            position: absolute;
            top: 430px;
            left: 40px;
            .date {
              font-size: 18px;
            }
            .p {
              font-size: 12px;
              color: black;
              width: 420px;
            }
          }
          .time{
            .months {
              position: relative;
              top: 280px;
              left: 40px;
              .select-1 {
                width: 200px;
              }
              .select-1:focus {
                border-color: #1da1f2;
              }
            }
            .days {
              position: relative;
              top: 230px;
              left: 250px;
              .select-2 {
                width: 70px;
                &::-webkit-scrollbar{
                  width:8px;
                }
                &::-webkit-scrollbar-thumb{
                    background-color: #2F3336;
                    border-radius: 5px;
                    border:none;
                }
                &::-webkit-scrollbar-track{
                    background:none;
                    border:none;
                }
                &:focus{
                    outline:none;
                }
              }
              .select-2:focus {
                border-color: #1da1f2;
              }
            }
            .years {
              position: relative;
              top: 180px;
              left: 330px;
              .select-3 {
                width: 130px;
                &::-webkit-scrollbar{
                  width:8px;
                }
                &::-webkit-scrollbar-thumb{
                    background-color: #2F3336;
                    border-radius: 5px;
                    border:none;
                }
                &::-webkit-scrollbar-track{
                    background:none;
                    border:none;
                }
                &:focus{
                    outline:none;
                }
              }
              .select-3:focus {
                border-color: #1da1f2;
              }
            }
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
              top: 600px;
              cursor: pointer;
              color: white;
              transition: all 0.3s;
            }
            .next:disabled{
              color: gray;
              background-color: #0e537e;
            }
            .next:not(:disabled):hover {
              background-color: #2394db;
            }
          }
          .login-link {
            position: absolute;
            top: 645px;
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
        height: 560px;
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
            .name {
              top: 72px;
              left: 30px;
              width: 390px;
            }
            .username {
              top: 87px;
              left: 30px;
              width: 390px;
            }
            .email {
              top: 102px;
              left: 30px;
              width: 390px;
            }
            .password {
              top: 117px;
              left: 30px;
              width: 390px;
            }
            .confirm_password {
              top: 132px;
              left: 30px;
              width: 390px;
            }
            .birth_date {
              top: 330px;
              left: 30px;
              .date {
                font-size: 16px;
              }
              .p {
                font-size: 11px;
                width: 390px;
              }
            }
            .time{
              select {
                height: 40px;
                font-size: 12px;
              }
              .months {
                top: 230px;
                left: 30px;
                .select-1 {
                  width: 170px;
                }
              }
              .days {
                top: 190px;
                left: 210px;
                .select-2 {
                  width: 70px;
                }
              }
              .years {
                top: 150px;
                left: 290px;
                .select-3 {
                  width: 130px;
                }
              }
            }
            .submit-form{
              .next {
                font-size: 12px;
                width: 390px;
                height: 35px;
                left: 30px;
                top: 490px;
              }
            }
            .login-link {
              top: 535px;
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
        height: 650px;
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
            .name {
              top: 70px;
              left: 40px;
              width: 470px;
            }
            .username {
              top: 85px;
              left: 40px;
              width: 470px;
            }
            .email {
              top: 100px;
              left: 40px;
              width: 470px;
            }
            .password {
              top: 115px;
              left: 40px;
              width: 470px;
            }
            .confirm_password {
              top: 130px;
              left: 40px;
              width: 470px;
            }
            .birth_date {
              top: 380px;
              left: 40px;
              .date {
                font-size: 18px;
              }
              .p {
                font-size: 13px;
                width: 470px;
              }
            }
            .time{
              select {
                height: 50px;
                font-size: 14px;
              }
              .months {
                top: 230px;
                left: 40px;
                .select-1 {
                  width: 200px;
                }
              }
              .days {
                top: 180px;
                left: 250px;
                .select-2 {
                  width: 90px;
                }
              }
              .years {
                top: 130px;
                left: 350px;
                .select-3 {
                  width: 160px;
                }
              }
            }
            .submit-form{
              .next {
                font-size: 14px;
                width: 470px;
                height: 50px;
                left: 40px;
                top: 560px;
              }
            }
            .login-link {
              top: 620px;
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
        height: 600px;
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
            .name {
              top: 70px;
              left: 30px;
              width: 440px;
            }
            .username {
              top: 85px;
              left: 30px;
              width: 440px;
            }
            .email {
              top: 100px;
              left: 30px;
              width: 440px;
            }
            .password {
              top: 115px;
              left: 30px;
              width: 440px;
            }
            .confirm_password {
              top: 130px;
              left: 30px;
              width: 440px;
            }
            .birth_date {
              top: 350px;
              left: 30px;
              .date {
                font-size: 17px;
              }
              .p {
                font-size: 12px;
                width: 440px;
              }
            }
            .time{
              select {
                height: 45px;
                font-size: 13px;
              }
              .months {
                top: 230px;
                left: 30px;
                .select-1 {
                  width: 190px;
                }
              }
              .days {
                top: 185px;
                left: 230px;
                .select-2 {
                  width: 80px;
                }
              }
              .years {
                top: 140px;
                left: 320px;
                .select-3 {
                  width: 150px;
                }
              }
            }
            .submit-form{
              .next {
                font-size: 13px;
                width: 440px;
                height: 40px;
                left: 30px;
                top: 520px;
              }
            }
            .login-link {
              top: 575px;
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
        height: 560px;
        .heading_create {
          font-size: 12px;
        }
        .div-form{
          .form{
            input[type="text"], input[type="password"] {
              height: 40px;
              width: 390px;
            }
            .name {
              top: 70px;
              width: 390px;
            }
            .username {
              top: 85px;
              width: 390px;
            }
            .email {
              top: 100px;
              width: 390px;
            }
            .password {
              top: 115px;
              width: 390px;
            }
            .confirm_password {
              top: 130px;
              width: 390px;
            }
            .birth_date {
              top: 330px;
              .date {
                font-size: 16px;
              }
              .p {
                font-size: 11px;
                width: 390px;
              }
            }
            .time{
              select {
                height: 40px;
                font-size: 12px;
              }
              .months {
                top: 230px;
                .select-1 {
                  width: 170px;
                }
              }
              .days {
                top: 190px;
                left: 210px;
                .select-2 {
                  width: 70px;
                }
              }
              .years {
                top: 150px;
                left: 290px;
                .select-3 {
                  width: 130px;
                }
              }
            }
            .submit-form{
              .next {
                font-size: 12px;
                width: 390px;
                height: 35px;
                top: 490px;
              }
            }
            .login-link {
              top: 535px;
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
            .name {
              width: 340px;
            }
            .username {
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
            .birth_date {
              .p {
                width: 340px;
              }
            }
            .time{
              .months {
                .select-1 {
                  width: 150px;
                }
              }
              .days {
                left: 190px;
                .select-2 {
                  width: 60px;
                }
              }
              .years {
                left: 260px;
                .select-3 {
                  width: 110px;
                }
              }
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
        height: 500px;
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
            .name {
              top: 60px;
              left: 20px;
              width: 310px;
            }
            .username {
              top: 75px;
              left: 20px;
              width: 310px;
            }
            .email {
              top: 90px;
              left: 20px;
              width: 310px;
            }
            .password {
              top: 105px;
              left: 20px;
              width: 310px;
            }
            .confirm_password {
              top: 120px;
              left: 20px;
              width: 310px;
            }
            .birth_date {
              top: 300px;
              left: 20px;
              .date {
                font-size: 14px;
              }
              .p {
                font-size: 10px;
                width: 310px;
              }
            }
            .time{
              select {
                height: 35px;
                font-size: 11px;
              }
              .months {
                top: 210px;
                left: 20px;
                .select-1 {
                  width: 140px;
                }
              }
              .days {
                top: 175px;
                left: 170px;
                .select-2 {
                  width: 50px;
                }
              }
              .years {
                top: 140px;
                left: 230px;
                .select-3 {
                  width: 100px;
                }
              }
            }
            .submit-form{
              .next {
                font-size: 11px;
                width: 310px;
                height: 30px;
                left: 20px;
                top: 440px;
              }
            }
            .login-link {
              top: 480px;
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
        height: 410px;
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
            .name {
              top: 55px;
              left: 15px;
              width: 270px;
            }
            .username {
              top: 65px;
              left: 15px;
              width: 270px;
            }
            .email {
              top: 75px;
              left: 15px;
              width: 270px;
            }
            .password {
              top: 85px;
              left: 15px;
              width: 270px;
            }
            .confirm_password {
              top: 95px;
              left: 15px;
              width: 270px;
            }
            .birth_date {
              top: 240px;
              left: 15px;
              .date {
                font-size: 13px;
              }
              .p {
                font-size: 9px;
                width: 270px;
              }
            }
            .time{
              select {
                height: 30px;
                font-size: 10px;
              }
              .months {
                top: 160px;
                left: 15px;
                .select-1 {
                  width: 130px;
                }
              }
              .days {
                top: 130px;
                left: 155px;
                .select-2 {
                  width: 45px;
                }
              }
              .years {
                top: 100px;
                left: 210px;
                .select-3 {
                  width: 75px;
                }
              }
            }
            .submit-form{
              .next {
                font-size: 10px;
                width: 270px;
                height: 30px;
                left: 15px;
                top: 360px;
              }
            }
            .login-link {
              top: 395px;
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
            .name {
              width: 240px;
            }
            .username {
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
            .birth_date {
              .p {
                width: 240px;
              }
            }
            .time{
              .months {
                .select-1 {
                  width: 120px;
                }
              }
              .days {
                left: 145px;
                .select-2 {
                  width: 45px;
                }
              }
              .years {
                left: 200px;
                .select-3 {
                  width: 55px;
                }
              }
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