export  const useUserSession = () => {
    const user = useSanctumUser<IUser>();

    const userCookie = useCookie<IUser | null> ('user_session', {
        maxAge: 60 * 60,
        watch: true
    });

    const userState = useState<IUser | null>('user', () => userCookie.value);

    watch(user, (newUser) => {
        userState.value = newUser;
        userCookie.value = newUser;
    }, {immediate: true});

    return{
        user: userState
    };
};