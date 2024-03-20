<template>
    <div class="content-container">
        <div class="black-line"></div>
        <div class="top-bar">
            <button class="back-icon" @click="goBack">
                <ion-icon name="arrow-back-outline"></ion-icon>
            </button>
            <div class="title">Who to follow</div>
        </div>

        <div class="people-container">
            <div class="person" v-for="person in users" :key="person.UserID">
                <div class="user">
                    <div class="user-img">
                        <img @click.stop="openProfile(person.UserTag)" :src="'/storage/' + person.ProfilePicture" alt="" class="person-img"> 
                    </div>
                    <div class="user-info">
                        <p class="username">{{ person.Name }}</p>
                        <p class="usertag">{{ person.UserTag }}</p>
                    </div>
                </div>
                <div class="button-container">
                    <button  
                        class="follow-button" 
                        @click="toggleFollowUnfollow(person.UserID)"
                        v-if="!(person.UserID === user.UserID)"
                        :class="{
                            'followed-button': person.isFollowedByMe,
                            'unfollow-button': person.isFollowedByMe && isHovered[person.UserID]
                        }"
                        @mouseover="isHovered[person.UserID] = true"
                        @mouseout="isHovered[person.UserID] = false">
                        {{ followButtonLabel(person) }}
                    </button>
                </div>
            </div>
        </div>
    </div>
</template>
<script>
import { mapState } from 'vuex';
export default {
    name: 'PeopleContent',
    data: () => ({
        showTweetWindow: false,
        followed: false,
        users:[],
        isHovered:  [],
    }),
    computed: {
        ...mapState(['user']),
        followButtonLabel() {
            return (person) => {
                if (this.isHovered[person.UserID] && person.isFollowedByMe) {
                    return 'Unfollow';
                } else if (person.isFollowedByMe) {
                    return 'Following';
                } else {
                    return 'Follow';
                }
            };
        },
    },
    methods: {
        goBack() {
            this.$router.go(-1);
        },
        openProfile(tag){
            const NoSymbolTag = tag.replace(/^@/, '');
            this.$router.push('/profile/' + NoSymbolTag);
            console.log(tag);
        },
        toggleFollowUnfollow(userID) {
            const user = this.users.find((t) => t.UserID === userID);
            if (user.isFollowedByMe) {
                this.handleUnfollow(userID);
            } else {
                this.handleFollow(userID);
            }
        },
        async handleFollow(userID) {
            try {
                const response = await this.$axios.post(`/api/follow/${userID}`);
                console.log('Follow Response:', response);
                if (response.status === 200) {
                    const user = this.users.find((t) => t.UserID === userID);
                    user.isFollowedByMe = true;
                }
            } catch (error) {
                console.error('Error following the user:', error);
            }
        },

        async handleUnfollow(userID) {
            try {
                const response = await this.$axios.post(`/api/unfollow/${userID}`);
                console.log('Unfollow Response:', response);
                if (response.status === 200) {
                    const user = this.users.find((t) => t.UserID === userID);
                    user.isFollowedByMe = false;
                }
            } catch (error) {
                console.error('Error unfollowing the user:', error);
            }
        },
    },
    async mounted() {
        await this.$store.dispatch('initializeApp');
        this.$axios.get('/api/allusers')
        .then(response => {
            this.users = response.data;
        })
        .catch(error => {
            console.error(error);
        });
    }, 
}
</script>
<style lang="scss" scoped>
// .tweets-container {
//     width: 100%;
//     height: auto;
//     color: white;
// }
.black-line{
    position:fixed;
    top:0;
    left:0;
    right:0;
    height:2px;
    z-index:8;
    background-color: black;
}
.top-bar {
    height: 60px;
    width:100%;
    background-color: rgba($color: #000000, $alpha: 0.8);
    border-bottom:solid 1px #2F3336;
    position: sticky;
    top: 0;
    z-index: 9;
    box-sizing: border-box;
    backdrop-filter: blur(5px);
    display: flex;
    align-items: center;
    padding: 0 10px;
}

.title {
    width: 100%;
    height: 28px;
    display: flex;
    align-items: center;
    justify-content: flex-start;
    box-sizing: border-box;
    padding-left: 20px;
    font-weight: bold;
    font-size: 22px;
}

.people-container{
    box-sizing: border-box;
    width:100%;
    height:auto;
    display:flex;
    flex-direction: column;
    .person{
        box-sizing: border-box;
        padding:10px 20px;
        width:100%;
        height:auto;
        display:flex;
        flex-direction: row;
        justify-content: space-between;
        align-items: center;
        transition:all 0.3s;
        cursor:pointer;
        .user{
            width:auto;
            height:100%;
            display:flex;
            flex-direction: row;
            align-items: center;
            justify-content: flex-start;
            box-sizing: border-box;
            .user-img{
                width:auto;
                height:auto;
                display:flex;
                align-items: center;
                justify-content: center;
                img{
                    width:50px;
                    height:50px;
                    border-radius:50%;
                    background-color: #ffffff;
                }
            }
            .user-info{
                padding-left:10px;
                width:100%;
                height:100%;
                display:flex;
                flex-direction: column;
                align-items: flex-start;
                justify-content: center;
                gap:7px;
                .username{
                    max-width:150px;
                    overflow: hidden;
                    white-space: nowrap;
                    text-overflow: ellipsis;
                    font-weight: bold;
                    font-size: 16px;
                    margin:0;
                }
                .usertag{
                    max-width:150px;
                    overflow: hidden;
                    white-space: nowrap;
                    color: #71767B;
                    font-size: 16px;
                    margin:0;
                }
            }
        }
        .button-container{
            width:auto;
            height:100%;
            display:flex;
            align-items: center;
            justify-content: center;
        }
        &:hover{
            background-color: rgba($color: #1a1a1a, $alpha: 0.9);
        }
    }
}

.back-icon {
    display: flex;
    align-items: center;
    justify-content: center;
    box-sizing: border-box;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    border: none;
    font-size: 22px;
    background:none;
    color: white;
    cursor: pointer;
    transition: all 0.3s;
    &:hover {
        background-color: rgba($color: #1a1a1a, $alpha: 0.9);
    }
}
@media (max-width: 500px) {
.top-bar{
    height:35px;
    width:100%;
    .search-input {
        width: 100%;
        height: 80%;
        border-radius: 50px;
        padding-left:30px;
        border:  1px solid transparent;
        background-color: #202327;
        position: relative;
        color:white;
        font-size: 13px;
    }

    .search-icon {
        position: absolute;
        left: 30px;
        top: 50%;
        transform: translate(0, -50%);
        color: #71767B;
        font-size: 16px;
    }
}

.title{
    font-size:17px !important;
}
.title2{
    width:100%;
    box-sizing: border-box;
    height:70%;
}
.people-container{
    .person{
        padding:10px 20px;
        .user{
            .user-img{
                img{
                    width:40px;
                    height:40px;
                }
            }
            .user-info{
                display:flex;
                gap:5px;
                .username{
                    font-size: 14px;
                }
                .usertag{
                    font-size: 14px;
                }
            }
        }
    }
}
}
@media (max-width: 375px) {
.people-container{
    .person{
        padding:10px 20px;
        .user{
            .user-img{
                img{
                    width:40px;
                    height:40px;
                }
            }
            .user-info{
                display:flex;
                gap:5px;
                .username{
                    max-width:90px;
                    font-size: 12px;
                }
                .usertag{
                    max-width:90px;
                    font-size: 12px;
                }
            }
        }
    }
}
}
</style>
