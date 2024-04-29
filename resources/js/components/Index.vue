<template>
  <div class="index-container">
    <div class="left-side">
      <ion-icon name="logo-yahoo" class="y-logo1"></ion-icon>
    </div>
    <div class="right-side">
      <div class="sign-container">
        <div class="logo-container">
          <ion-icon name="logo-yahoo" class="y-logo2"></ion-icon>
        </div>
        <p class="title-welcome">Welcome</p>
        <div class="sign-up-container">
          <p class="title-join">Join today.</p>
          <button class="create-account-btn" @click="goToRegister">Create an account</button>
        </div>
        <div class="line">
          <div class="left"></div>
          <span class="text">or</span>
          <div class="right"></div>
        </div>
        <div class="sign-in-container">
          <p class="title-already">Already have an account?</p>
          <button class="sign-in-btn" @click="goToLogin">Sign in</button>
        </div>
      </div>
    </div>
  </div>
</template>
  
<script>
  export default {
    name: 'Index',
    methods: {
      goToRegister() {
        this.$router.push('/register');
      },
      goToLogin() {
        this.$router.push('/login');
      }
    }
  }
</script>
  
<style lang="scss" scoped>
  .index-container{
    position:fixed;
    top:0;
    bottom:0;
    left:0;
    right:0;
    display:flex;
    flex-direction: row;
    box-sizing: border-box;
    padding:80px 0px;
    color:black;
    .left-side{
      width:55%;
      height:100%;
      display:flex;
      align-items: center;
      justify-content: center;
      .y-logo1{
        font-size:400px;
      }
    }
    .right-side{
      width:45%;
      height:100%;
      display:flex;
      flex-direction: column;
      .sign-container{
        display:flex;
        flex-direction: column;
        justify-content: center;
        width:400px;
        height:100%;
        .title-welcome{
          font-size:80px;
          font-weight:600;
          margin:0;
        }
        .sign-up-container{
          display:flex;
          flex-direction: column;
          gap:10px;
          padding:30px 0px;
          .title-join{
            font-size:40px;
            font-weight:600;
            margin:0px;
            padding-bottom:20px;
          }
          .create-account-btn{
            width:100%;
            height:45px;
            border-radius: 50px;
            border:3px solid black;
            background-color: white;
            color:black;
            display:flex;
            flex-direction: row;
            justify-content: center;
            align-items: center;
            gap:5px;
            font-size:16px;
            font-weight:bold;
            transition:0.3s;
            cursor:pointer;
            &:hover{
              background-color:#f2f2f2;
            }
          }
        }
        .line{
          display: flex;
          justify-content: center;
          align-items: center;
          width: 100%;
          color: gray;
          .left, .right{
            flex: 1;
            height: 1px;
            background-color: gray;
          }
          .text{
            font-size: 18px;
            padding:0 10px;
          }
        }
        .sign-in-container{
          display:flex;
          flex-direction: column;
          .title-already{
            font-size:20px;
            font-weight: 600;
          }
          .sign-in-btn{
            width:100%;
            height:45px;
            border-radius: 50px;
            border: 3px solid black;
            background-color: white;
            color:black;
            display:flex;
            flex-direction: row;
            justify-content: center;
            align-items: center;
            gap:5px;
            font-size:16px;
            font-weight:bold;
            transition:0.3s;
            cursor:pointer;
            &:hover{
              background-color: #f2f2f2;
            }
          }
        }
      }
    }
  }

  @media (min-width: 450px) {
    .y-logo2{
      display:none;
    }
  }
  @media (max-width: 1000px) {
    .index-container{
      .y-logo1{
        display:none;
      }
      .left-side{
        display:none;
      }
      .right-side{
        width:100%;
        align-items:center;
      }
    }
  }
  @media (max-width: 450px) {
    .index-container{
      padding:0px 10px;
      .y-logo1{
        display:none;
      }
      .y-logo2{
        display:block;
        font-size:50px;
      }
      .left-side{
        display:none;
      }
      .right-side{
        text-align: center;
        .sign-container{
          width:100%;
          .logo-container{
            display:flex;
            justify-content: center;
            align-items: center;
          }
          .title-welcome{
            font-size:40px;
            font-weight:600;
            margin:0;
          }
          .sign-up-container{
            gap:5px;
            padding:15px 0px;
            .title-join{
              font-size:30px;
              font-weight:600;
              margin:0px;
              padding-bottom:10px;
            }
            .create-account-btn{
              width:100%;
              height:30px;
              border-radius: 50px;
              gap:5px;
              font-size:13px;
              font-weight:bold;
            }
          }
          .line{
            .left, .right{
              flex: 1;
              height: 1px;
            }
            .text{
              font-size: 12px;
              padding:0 5px;
            }
          }
          .sign-in-container{
            .title-already{
              font-size:15px;
              font-weight: 600;
              margin:0;
              padding:10px 0;
            }
            .sign-in-btn{
              width:100%;
              height:30px;
              border-radius: 50px;
              gap:5px;
              font-size:13px;
              font-weight:bold;
            }
          }
        }
      }
    }
  }
</style>