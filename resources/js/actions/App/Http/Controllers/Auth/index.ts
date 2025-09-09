import ApiAuthController from './ApiAuthController'
import RegisteredUserController from './RegisteredUserController'
import AuthenticatedSessionController from './AuthenticatedSessionController'
import OtpController from './OtpController'
import VerifyEmailController from './VerifyEmailController'
const Auth = {
    ApiAuthController: Object.assign(ApiAuthController, ApiAuthController),
RegisteredUserController: Object.assign(RegisteredUserController, RegisteredUserController),
AuthenticatedSessionController: Object.assign(AuthenticatedSessionController, AuthenticatedSessionController),
OtpController: Object.assign(OtpController, OtpController),
VerifyEmailController: Object.assign(VerifyEmailController, VerifyEmailController),
}

export default Auth