<template>
    <div class="content-container">
        <div class="top-bar">
            <button class="back-btn" @click="goBack">
                <ion-icon name="arrow-back-outline"></ion-icon>
            </button>
            <div class="title">Post</div>
        </div>
        <div class="post-container">
            <div class="post" v-if="tweet && tweet.user">
                <div class="isretweet" v-if="tweet.isRetweet">
                    <p class="tweet-text"><span>{{ user.UserTag }}</span> Reposted</p>
                </div>
                <div class="inner-post">
                    <div class="left-side">
                        <img @click.stop="openProfile(tweet.user.UserTag)" :src="'/storage/' + tweet.user.ProfilePicture">
                        <div class="horizontal-comment-line" v-if="postType === 'replies'"></div>
                    </div>
                    <div class="right-side">
                        <div class="top2">
                            <div class="person-image">
                                <img @click.stop="openProfile(tweet.user.UserTag)" :src="'/storage/' + tweet.user.ProfilePicture">
                                <div class="horizontal-comment-line" v-if="postType === 'replies'"></div>
                            </div>
                            <div class="info-content">
                                <div class="userinfo">
                                    <p class="username">{{tweet.user.Name}}</p>
                                    <p class="usertag">{{tweet.user.UserTag}}</p>
                                    <p class="time-posted">{{ tweet.created_ago }}</p>
                                </div>
                                <div class="content-text">
                                    <p v-if="tweet.TweetText" v-html="(tweet.TweetText)"></p>
                                </div>
                            </div>
                        </div>
                        <div class="post-top">
                            <div class="userinfo">
                                <p class="username">{{tweet.user.Name}}</p>
                                <p class="usertag">{{tweet.user.UserTag}}</p>
                                <p class="time-posted">{{ tweet.created_ago }}</p>
                            </div>
                            <div class="content-text">
                                <p v-if="tweet.TweetText" v-html="(tweet.TweetText)"></p>
                            </div>
                        </div>
                        <div class="content-img">
                            <img v-if="tweet.TweetImage" :src="'/storage/' + tweet.TweetImage"/>
                        </div>
                        <div class="bottom">
                            <button class="post-btn-container heart-btn" @click.stop="toggleLike(tweet.TweetID)">
                                <div class="icon-container"><ion-icon :name="tweet.isLiked ? 'heart' : 'heart-outline'" class="post-icon" :class ="{ 'liked': tweet.isLiked }"></ion-icon></div>
                                <p class="post-btn-nr" :class ="{ 'liked': tweet.isLiked }">{{ tweet.like_count }}</p>
                            </button>
                            <button class="post-btn-container comment-btn" @click.stop="tweetIdInPopup = tweet.TweetID; TogglePopup('CommentTrigger')">
                                <div class="icon-container"><ion-icon name="chatbox-outline" class="post-icon"></ion-icon></div>
                                <p class="post-btn-nr">{{ tweet.comment_count }}</p>
                            </button>
                            <button class="post-btn-container retweet-btn" @click.stop="toggleRetweet(tweet.TweetID)">
                                <div class="icon-container"><ion-icon :name="tweet.isRetweeted ? 'arrow-redo' : 'arrow-redo-outline'" class="post-icon" :class ="{ 'retweeted': tweet.isRetweeted }"></ion-icon></div>
                                <p class="post-btn-nr" :class ="{ 'retweeted': tweet.isRetweeted }">{{ tweet.retweet_count }}</p>
                            </button>
                            <button class="post-btn-container bookmark-btn" @click.stop="toggleBookmark(tweet.TweetID)">
                                <div class="icon-container">
                                    <ion-icon :name="tweet.isBookmarked ? 'bookmark' : 'bookmark-outline'" class="post-icon" :class="{ 'bookmarked': tweet.isBookmarked }"></ion-icon>
                                </div>
                                <p class="post-btn-nr" :class="{ 'bookmarked': tweet.isBookmarked }"></p>
                            </button>
                        </div>
                    </div>
                </div>
            </div>
            <div class="create-tweet-or-comment" v-if="user">
                <div class="left-side">
                    <img @click.stop="openProfile(user.UserTag)" :src="'/storage/' + user.ProfilePicture">
                </div>
                <div class="right-side">
                    <div class="replyinginfo">                   
                        <p class="replying-to">Replying to </p>
                        <p class="profiletag" v-if="tweet && tweet.user">{{ tweet.user.UserTag }}</p>
                    </div>
                    <div class="top">
                        <div class="tweet-input-container">
                            <textarea v-model="main_comment_text_input" id="tweet-input" class="tweet-input" rows="1" placeholder="Post your reply" @input="autoSize('mainInput')" ref="mainInput" maxlength="255"></textarea>
                        </div>
                    </div>
                    <div class="bottom">
                        <div class="buttons"></div>
                        <button class="post-button" @click="createComment(tweet.TweetID, main_comment_text_input, mainInput)" :disabled="buttonDisabled || !main_comment_text_input">Reply</button>
                    </div>
                </div>
            </div>
        </div>
        <div class="comment-container">
            <div class="comment" v-for="comment in comments" :key="comment.CommentID">
                <div class="left" v-if="comment.user">
                    <img @click.stop="openProfile(comment.user.UserTag)" :src="'/storage/' + comment.user.ProfilePicture">
                    <div class="content">
                        <div class="userinfo">
                            <p class="username">{{ comment.user.Name }}</p>
                            <p class="usertag">{{ comment.user.UserTag }}</p>
                            <p class="time-posted">{{ comment.created_ago }}</p>
                        </div>
                        <div class="content-text">
                            <p v-if="comment.CommentText" v-html="(comment.CommentText)"></p>
                        </div>
                    </div>
                </div>
                <button class="delete-btn" @click.stop="deleteCommentTweetID = comment.TweetID; deleteCommentID = comment.CommentID; TogglePopup('DeleteTrigger2')" v-if="comment.user && comment.user.UserID === user.UserID">
                    <ion-icon name="trash-bin-outline" class="delete-icon"></ion-icon>
                </button>
            </div>
        </div>
    </div>
    <Popup v-if="popupTriggers.CommentTrigger" :TogglePopup="() => TogglePopup('CommentTrigger')">
        <div class="create-tweet-or-comment-popup">
            <div class="top">
                <div class="left-side-popup">
                    <img  @click="openProfile(user.UserTag)" :src="'/storage/' + user.ProfilePicture">
                </div>
                <div class="right-side-popup">
                    <div class="userinfo-popup">
                        <p class="username">{{ user.Name }}</p>
                        <p class="usertag">{{ user.UserTag }}</p>
                    </div>
                    <div class="tweet-input-container">
                        <textarea v-model="popup_comment_text_input" id="tweet-input-comment" class="tweet-input" rows="1" placeholder="Post your reply" @input="autoSize('popupInput')" ref="popupInput" maxlength="255"></textarea>
                    </div>
                </div>
            </div>
            <div class="bottom">
                <div class="buttons"></div>
                <button class="popup-button" @click="createComment(tweetIdInPopup, popup_comment_text_input, popupInput)" :disabled="buttonDisabled || !popup_comment_text_input">Comment</button>
            </div>
        </div>
    </Popup>
    <Popup v-if="popupTriggers.DeleteTrigger2" :TogglePopup="() => TogglePopup('DeleteTrigger2')">
        <div class="delete-popup">
            <h1 class="delete-title">Delete Comment</h1>
            <p class="tweet-p">Are you sure you want to delete this comment?</p>
            <div class="tweet-buttons">
                <button class="cancel-button" @click="TogglePopup('DeleteTrigger2')">Cancel</button>
                <button class="delete-button" @click="deleteComment(deleteCommentTweetID, deleteCommentID)">Delete</button>
            </div>
        </div>
    </Popup>
</template>

<script>
    import { ref } from 'vue';
    import Popup from '../Popup.vue';
    import axios from 'axios';
    import { mapState } from 'vuex';
    
    export default {
        name: 'PostsContent',

        components: {
            Popup,
        },

        data() {
            return {
                isInputFocused: false,
                tweet: null,
                comments: [],
                users: [],
                filteredUsers: [],
                buttonDisabled: false,
                deleteCommentTweetID: null,
                deleteCommentID: null,
            };
        },
        
        setup() {
            const main_comment_text_input = ref('');
            const popup_comment_text_input = ref('');
            const mainInput = ref(null);
            const popupInput = ref(null);
            const popupTriggers = ref({
                CommentTrigger: false,
                DeleteTrigger2: false,
            });

            const TogglePopup = (trigger, source) => {
                popupTriggers.value[trigger] = !popupTriggers.value[trigger];
            }

            return {
                popupTriggers,
                TogglePopup,
                main_comment_text_input,
                popup_comment_text_input,
                mainInput,
                popupInput,
            }
        },

        computed: {
            ...mapState(['user']),
        },

        methods: {
            async updateCounts(tweets) {
                try {
                    const tweetIds = tweets.map(tweet => tweet.TweetID);
                    const response = await axios.get(`/api/update-stats`, {
                        params: {
                            tweetIds: tweetIds,
                        }
                    });

                    const updatedStatsMap = response.data;

                    tweets.forEach(tweet => {
                        const updatedStats = updatedStatsMap[tweet.TweetID];
                        if (updatedStats) {
                            tweet.like_count = updatedStats.like_count;
                            tweet.comment_count = updatedStats.comment_count;
                            tweet.retweet_count = updatedStats.retweet_count;
                            tweet.created_ago = updatedStats.created_ago;
                        }
                    });

                } catch (error) {
                    console.error('Error updating counts:', error);
                }
            },

            fetchCommentsByTweet(tweetID) {
                fetch(`api/comments/${tweetID}`)
                .then((response) => response.json())
                .then((data) => {
                    this.comments = data.comments;
                })
                .catch((error) => {
                    console.error('Error fetching comments:', error);
                });
            },

            async createComment(tweetID, commentText) {
                if (this.buttonDisabled) {
                    return;
                }
                if (commentText.trim() === '') {
                    return;
                }
                try {
                    this.buttonDisabled = true;
                    const response = await this.$axios.post('/api/create-comments', {
                        tweetId: tweetID,
                        commentText: commentText,
                    });
                    const newComment = response.data.comment;
                    this.comments.unshift(newComment);
                    this.tweet.comment_count++;
                    this.main_comment_text_input = '';
                    this.popup_comment_text_input = '';
                    const textarea1 = this.mainInput;
                    const textarea2 = this.popupInput;
                    if (textarea2){
                        textarea2.style.height = 'auto';
                    }
                    textarea1.style.height = 'auto';

                    this.popupTriggers.CommentTrigger = false;
                    setTimeout(() => {
                        this.buttonDisabled = false;
                    }, 2000);
                } catch (error) {
                    console.error('Error creating comment:', error);
                    this.buttonDisabled = false;
                }
            },

            deleteComment(tweetID, commentID) {
                this.performDeleteComment(tweetID, commentID);
            },

            async performDeleteComment(tweetID, commentID) {
                if (!commentID) {
                    console.error('Comment ID not provided');
                    return;
                }
                try {
                    await this.$axios.delete(`/api/delete-comments/${commentID}`);
                    this.comments = this.comments.filter((comment) => comment.CommentID !== commentID);
                    this.tweet.comment_count--;
                    this.fetchCommentsByTweet(tweetID);
                    this.popupTriggers.DeleteTrigger2 = false;
                    console.log(`Comment with ID ${commentID} deleted successfully.`);
                } catch (error) {
                    console.error('Error deleting comment:', error);
                }
            },

            openProfile(tag){
                const NoSymbolTag = tag.replace(/^@/, '');
                this.$router.push('/profile/' + NoSymbolTag);
                console.log(tag);
            },

            autoSize(ref) {
                const maxRows = 8;
                const textarea = this.$refs[ref];
                textarea.style.height = 'auto';
                const customLineHeight = 1;
                const maxHeight = maxRows * customLineHeight * parseFloat(getComputedStyle(textarea).fontSize);

                if (textarea.scrollHeight <= maxHeight) {
                    textarea.style.height = textarea.scrollHeight + 'px';
                } else {
                    textarea.style.height = maxHeight + 'px';
                }
            },

            goBack() {
                this.$router.go(-1);
            },

            toggleLike(tweetID) {
                const tweet = this.tweet;
                if (this.buttonDisabled) {
                    return;
                }
                if (!tweet) {
                    return;
                }
                if (tweet.isLiked) {
                    this.buttonDisabled = true;
                    this.unlikeTweet(tweet.TweetID);
                    setTimeout(() => {
                        this.buttonDisabled = false;
                    }, 1500);
                } else {
                    this.buttonDisabled = true;
                    this.likeTweet(tweet.TweetID);
                    setTimeout(() => {
                        this.buttonDisabled = false;
                    }, 1500);
                }
            },

            async likeTweet(tweetID) {
                try {
                    const response = await this.$axios.post(`/api/tweets/like`, { tweetId: tweetID });
                    console.log('Like Response:', response);
                    if (response.status === 201) {
                        const tweet = this.tweet;
                        if (tweet) {
                            tweet.isLiked = true;
                            tweet.like_count += 1;
                        }
                    }
                } catch (error) {
                    console.error('Error liking the tweet:', error);
                }
            },

            async unlikeTweet(tweetId) {
                try {
                    const response = await this.$axios.delete(`/api/tweets/unlike/${tweetId}`);

                    console.log('Unlike Response:', response);

                    if (response.status === 200) {
                        const tweet = this.tweet;
                        if (tweet) {
                            tweet.isLiked = false;
                            tweet.like_count -= 1;
                        }
                    }
                } catch (error) {
                    console.error('Error unliking the tweet:', error);
                }
            },

            toggleRetweet(tweetID) {
                const tweet = this.tweet
                if (this.buttonDisabled) {
                    return;
                }
                if (!tweet) {
                    return;
                }
                if (tweet && tweet.user.UserTag === this.user.UserTag) {
                    return;
                }
                
                if (tweet.isRetweeted) {
                    this.buttonDisabled = true;
                    this.unretweetTweet(tweet.TweetID);
                    setTimeout(() => {
                        this.buttonDisabled = false;
                    }, 1500);
                } else {
                    this.buttonDisabled = true;
                    this.retweetTweet(tweet.TweetID);
                    setTimeout(() => {
                        this.buttonDisabled = false;
                    }, 1500);
                }
            },

            async retweetTweet(tweetID) {
                try {
                    const response = await this.$axios.post(`/api/tweets/retweet`, { tweetId: tweetID });

                    console.log('Retweet Response:', response);

                    if (response.status === 201) {
                        const tweet = this.tweet
                        if (tweet) {
                            tweet.isRetweeted = true;
                            tweet.retweet_count += 1;
                        }
                    }
                } catch (error) {
                    console.error('Error retweeting the tweet:', error);
                }
            },

            async unretweetTweet(tweetId) {
                try {
                    const response = await this.$axios.delete(`/api/tweets/unretweet/${tweetId}`);

                    console.log('Unretweet Response:', response);

                    if (response.status === 200) {
                        const tweet = this.tweet
                        if (tweet) {
                            tweet.isRetweeted = false;
                            tweet.retweet_count -= 1;
                        }
                    }
                } catch (error) {
                    console.error('Error unretweetin the tweet:', error);
                }
            },

            toggleBookmark(tweetID) {
                if (this.buttonDisabled) {
                    return;
                }
                const tweet = this.tweet;
                if (!tweet) {
                    return;
                }
                if (tweet.isBookmarked) {
                    this.buttonDisabled = true;
                    this.removeBookmark(tweet.TweetID);
                    setTimeout(() => {
                        this.buttonDisabled = false;
                    }, 1500);
                } else {
                    this.buttonDisabled = true;
                    this.createBookmark(tweet.TweetID);
                    setTimeout(() => {
                        this.buttonDisabled = false;
                    }, 1500);
                }
            },

            async createBookmark(tweetID) {
                try {
                    const response = await this.$axios.post(`/api/tweets/bookmark`, { tweetId: tweetID });
                    console.log('Bookmark Response:', response);
                    if (response.status === 201) {
                        const tweet = this.tweet;
                        if (tweet) {
                            tweet.isBookmarked = true;
                        }
                    }
                } catch (error) {
                    console.error('Error bookmarking the tweet:', error);
                }
            },

            async removeBookmark(tweetId) {
                try {
                    const response = await this.$axios.delete(`/api/tweets/unbookmark/${tweetId}`);
                    console.log('Unbookmark Response:', response);
                    if (response.status === 200) {
                        const tweet = this.tweet;
                        if (tweet) {
                            tweet.isBookmarked = false;
                        }
                    }
                } catch (error) {
                    console.error('Error unbookmarking the tweet:', error);
                }
            },

            getTweet(TweetID) {
                axios.get('/api/tweetdata/' + TweetID)
                .then(response => {
                    this.tweet = response.data.tweet;
                })
                .catch(error => {
                    console.error(error);
                });
            },
        },

        async mounted() {
            this.tweet = this.$route.params.tweet;
            await this.$store.dispatch('initializeApp');
            this.getTweet(this.$route.params.tweetID);
            this.fetchCommentsByTweet(this.$route.params.tweetID);
            this.NewTweetInterval = setInterval(() => {
                this.updateCounts([this.tweet]);
            }, 10000);
        },

        beforeDestroy() {
            clearInterval(this.NewTweetInterval);
        },

        beforeUnmount() {
            clearInterval(this.NewTweetInterval);
        },

        beforeRouteLeave(to, from, next) {
            clearInterval(this.NewTweetInterval);
        },
    };
</script>

<style lang="scss" scoped>
    .top-bar{
        height:60px;
        width:100%;
        background-color:rgba($color: white, $alpha: 0.8);
        border-bottom:solid 1px #2F3336;
        position:sticky;
        top:0;
        z-index:9;
        box-sizing: border-box;
        backdrop-filter: blur(5px);
        display: flex;
        align-items: center;
        padding:0 10px;
        .back-btn{
            display:flex;
            align-items: center;
            justify-content: center;
            box-sizing: border-box;
            width:40px;
            height:40px;
            border-radius: 50%;
            border:none;
            font-size:22px;
            background:none;
            color:black;
            cursor:pointer;
            transition: all 0.3s;
            &:hover{
                background-color: rgba($color: #f2f2f2, $alpha: 1);
            }
        }
        .title{
            width:100%;
            display:flex;
            align-items: center;
            justify-content: flex-start;
            box-sizing: border-box;
            padding-left:20px;
            font-weight: bold;
            font-size: 22px;
            color: black;
        }
    }
    .post-container{
        padding-bottom:0;
    }
    .comment-container{
        padding-bottom:80px;
        .comment{
            border-bottom: 1px solid black;
            border-image: none;
        }
    }
    @media (max-width: 500px) {
        .top-bar{
            width:100%;
            height:35px;
            .title{
                font-size:17px !important;
            }
        }
        .comment-container{
            padding-bottom:110px;
            .comment{
                padding:10px 10px 15px 10px;
            }
        }
    }
</style>